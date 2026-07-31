<?php

namespace App\Services\ProcesamientoMensual;

use App\Models\LoteMensual;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

class PrestamoOtroArchivoImportService
{
    private const COLUMNAS_REQUERIDAS = [
        'DESTINO',
        'PERSON',
        'CARNET',
        'GRADO',
        'NOMBRES',
        'PRESTAMO',
        'SER ADM',
    ];

    public function leer(UploadedFile $archivo, LoteMensual $lote): array
    {
        $this->validarNombre($archivo, $lote);

        try {
            $reader = IOFactory::createReaderForFile($archivo->getRealPath());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($archivo->getRealPath());
        } catch (Throwable $exception) {
            throw new InvalidArgumentException(
                'No se pudo leer la planilla. Verifique que sea un Excel válido.',
                previous: $exception
            );
        }

        try {
            if ($spreadsheet->getSheetCount() < 1) {
                throw new InvalidArgumentException('El libro no contiene hojas.');
            }

            $hoja = $spreadsheet->getSheet(0);
            $columnas = $this->resolverColumnas($hoja);
            $resultado = $this->leerRegistros($hoja, $columnas, $lote);

            if ($resultado['registros'] === []) {
                throw new InvalidArgumentException(
                    'La planilla no contiene filas con PRESTAMO o SER ADM mayores a cero.'
                );
            }

            return [
                'hash_sha256' => hash_file('sha256', $archivo->getRealPath()),
                'nombre_original' => $archivo->getClientOriginalName(),
                'extension' => Str::lower($archivo->getClientOriginalExtension()),
                'mime_type' => $archivo->getMimeType()
                    ?: $archivo->getClientMimeType(),
                'filas_importadas' => count($resultado['registros']),
                'filas_omitidas_sin_prestamo' => $resultado['omitidas'],
                'total_monto_descuento' => round(
                    array_sum(array_column($resultado['registros'], 'monto_descuento')),
                    6
                ),
                'total_tot_2' => round(
                    array_sum(array_column($resultado['registros'], 'tot_2')),
                    6
                ),
                'total_comision' => round(
                    array_sum(array_column($resultado['registros'], 'comision')),
                    6
                ),
                'registros' => $resultado['registros'],
            ];
        } finally {
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }
    }

    private function validarNombre(
        UploadedFile $archivo,
        LoteMensual $lote
    ): void {
        $nombre = $archivo->getClientOriginalName();

        if (preg_match(
            '/\Aplanilla_mindef_(\d{2})_(\d{4})\.(xlsx|xls)\z/i',
            $nombre,
            $coincidencias
        ) !== 1) {
            throw new InvalidArgumentException(
                'El nombre debe tener el formato planilla_mindef_MM_AAAA.xlsx.'
            );
        }

        $mes = (int) $coincidencias[1];
        $gestion = (int) $coincidencias[2];

        if ($mes !== (int) $lote->mes || $gestion !== (int) $lote->gestion) {
            throw new InvalidArgumentException(
                "El archivo corresponde a {$coincidencias[1]}/{$gestion}; "
                . "el lote corresponde a {$lote->codigo_periodo}."
            );
        }
    }

    private function resolverColumnas(Worksheet $hoja): array
    {
        $ultimaColumnaIndice = min(
            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
                $hoja->getHighestColumn()
            ),
            26
        );
        $columnas = [];

        for ($columna = 1; $columna <= $ultimaColumnaIndice; $columna++) {
            $encabezado = $this->normalizarEncabezado(
                $this->textoCelda($hoja->getCell([$columna, 1]))
            );

            if ($encabezado !== '') {
                $columnas[$encabezado] = $columna;
            }
        }

        $faltantes = array_values(array_diff(
            self::COLUMNAS_REQUERIDAS,
            array_keys($columnas)
        ));

        if ($faltantes !== []) {
            throw new InvalidArgumentException(
                'Faltan las columnas requeridas: ' . implode(', ', $faltantes) . '.'
            );
        }

        return $columnas;
    }

    private function leerRegistros(
        Worksheet $hoja,
        array $columnas,
        LoteMensual $lote
    ): array {
        $ultimaFila = $hoja->getHighestDataRow();

        if ($ultimaFila > 1000) {
            throw new InvalidArgumentException(
                'La planilla supera el límite de 1.000 filas.'
            );
        }

        $registros = [];
        $omitidas = 0;
        $codigosLeidos = [];

        for ($fila = 2; $fila <= $ultimaFila; $fila++) {
            $codigo = $this->normalizarCodigoPersonal(
                $this->valor($hoja, $columnas, 'PERSON', $fila)
            );
            $nombres = trim($this->valor($hoja, $columnas, 'NOMBRES', $fila));

            if ($codigo === '' || Str::upper(Str::ascii($nombres)) === 'TOTAL') {
                continue;
            }

            $prestamo = $this->decimal(
                $this->valor($hoja, $columnas, 'PRESTAMO', $fila)
            );
            $servicioAdministrativo = $this->decimal(
                $this->valor($hoja, $columnas, 'SER ADM', $fila)
            );

            if ($prestamo === null || $servicioAdministrativo === null) {
                throw new InvalidArgumentException(
                    "La fila {$fila} contiene un importe inválido en PRESTAMO o SER ADM."
                );
            }

            if ($prestamo < 0 || $servicioAdministrativo < 0) {
                throw new InvalidArgumentException(
                    "La fila {$fila} contiene importes negativos."
                );
            }

            if ($prestamo == 0.0 && $servicioAdministrativo == 0.0) {
                $omitidas++;
                continue;
            }

            if (isset($codigosLeidos[$codigo])) {
                throw new InvalidArgumentException(
                    "La papeleta {$codigo} está repetida en las filas "
                    . "{$codigosLeidos[$codigo]} y {$fila}."
                );
            }

            $codigosLeidos[$codigo] = $fila;
            $registros[] = [
                'fila_origen' => $fila,
                'gestion' => (int) $lote->gestion,
                'mes' => LoteMensual::MESES[$lote->mes],
                'documento_respaldo' => null,
                'eit_codorg' => null,
                'organismos' => $this->nuloSiVacio(
                    $this->valor($hoja, $columnas, 'DESTINO', $fila)
                ),
                'eit_codrep' => null,
                'reparticion' => null,
                'grupo' => '15',
                'descripcion_grupo' => 'PRESTAMOS COC.',
                'identificador_acreedor' => '171',
                'acreedor' => 'PRESTAMOS CAS RL.',
                'codigo_concepto' => '171',
                'codigo_acreedor' => null,
                'cta_bancaria_acreedor' => null,
                'codigo_personal' => $codigo,
                'eit_item' => null,
                'carnet' => $this->nuloSiVacio(
                    $this->valor($hoja, $columnas, 'CARNET', $fila)
                ),
                'grado' => $this->nuloSiVacio(
                    $this->valor($hoja, $columnas, 'GRADO', $fila)
                ),
                'mension' => null,
                'nombres' => $this->nuloSiVacio($nombres),
                // En la planilla principal MONTO_DESCUENTO representa el total
                // aplicado a la cuota y COMISION contiene SER ADM. Por tanto, la
                // planilla adicional debe conservar la misma semántica:
                // PRESTAMO + SER ADM = MONTO_DESCUENTO.
                'monto_descuento' => round(
                    $prestamo + $servicioAdministrativo,
                    6
                ),
                'tot_2' => round($prestamo, 6),
                'comision' => round($servicioAdministrativo, 6),
                'estado' => 'IMPORTADO',
                'observacion' => 'Origen: planilla adicional MinDef',
            ];
        }

        return ['registros' => $registros, 'omitidas' => $omitidas];
    }

    private function valor(
        Worksheet $hoja,
        array $columnas,
        string $nombre,
        int $fila
    ): string {
        return $this->textoCelda($hoja->getCell([$columnas[$nombre], $fila]));
    }

    private function textoCelda(Cell $celda): string
    {
        $valor = $celda->getCalculatedValue();

        if ($valor === null) {
            return '';
        }

        if (is_int($valor)) {
            return (string) $valor;
        }

        if (is_float($valor)) {
            return rtrim(rtrim(sprintf('%.10F', $valor), '0'), '.');
        }

        return trim((string) $valor);
    }

    private function decimal(string $valor): ?float
    {
        $valor = trim(str_replace(' ', '', $valor));

        if ($valor === '') {
            return 0.0;
        }

        if (str_contains($valor, ',') && ! str_contains($valor, '.')) {
            $valor = str_replace(',', '.', $valor);
        } else {
            $valor = str_replace(',', '', $valor);
        }

        return is_numeric($valor) ? (float) $valor : null;
    }

    private function normalizarEncabezado(string $valor): string
    {
        return preg_replace(
            '/\s+/',
            ' ',
            Str::upper(Str::ascii(trim($valor)))
        ) ?? '';
    }

    private function normalizarCodigoPersonal(string $valor): string
    {
        $valor = trim($valor);

        if (preg_match('/^\d+(?:\.0+)?$/', $valor)) {
            $valor = preg_replace('/\.0+$/', '', $valor) ?? $valor;
            $valor = ltrim($valor, '0');

            return $valor === '' ? '0' : $valor;
        }

        return $valor;
    }

    private function nuloSiVacio(string $valor): ?string
    {
        $valor = trim($valor);

        return $valor === '' ? null : $valor;
    }
}

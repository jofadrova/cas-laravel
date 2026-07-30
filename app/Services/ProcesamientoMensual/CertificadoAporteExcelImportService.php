<?php

namespace App\Services\ProcesamientoMensual;

use App\Models\LoteMensual;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

class CertificadoAporteExcelImportService
{
    private const COLUMNAS = [
        'GESTION',
        'MES',
        'DOCUMENTO_RESPALDO',
        'EIT_CODORG',
        'ORGANISMOS',
        'EIT_CODREP',
        'REPARTICION',
        'GRUPO',
        'DESCRION_GRUPO',
        'IDENTIFICADOR_ACREEDOR',
        'ACREEDOR',
        'CODIGO_CONCEPTO',
        'CODIGO_ACREEDOR',
        'CTA_BANCARIA_ACREEDOR',
        'CODIGO_PERSONAL',
        'EIT_ITEM',
        'CARNET',
        'GRADO',
        'MENSION',
        'NOMBRES',
        'MONTO_DESCUENTO',
        'TOT_2',
        'COMISION',
    ];

    public function leer(UploadedFile $archivo, LoteMensual $lote): array
    {
        try {
            $reader = IOFactory::createReaderForFile($archivo->getRealPath());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($archivo->getRealPath());
        } catch (Throwable $exception) {
            throw new InvalidArgumentException(
                'No se pudo leer el libro. Verifique que sea un archivo Excel válido.',
                previous: $exception
            );
        }

        try {
            if ($spreadsheet->getSheetCount() < 1) {
                throw new InvalidArgumentException('El libro no contiene hojas.');
            }

            $hoja = $spreadsheet->getSheet(0);
            $this->validarEncabezados($hoja);

            $registros = $this->leerRegistros($hoja, $lote);

            if ($registros === []) {
                throw new InvalidArgumentException(
                    'El archivo no contiene registros de certificados de aportes para importar.'
                );
            }

            return [
                'hash_sha256' => hash_file('sha256', $archivo->getRealPath()),
                'nombre_original' => $archivo->getClientOriginalName(),
                'extension' => strtolower($archivo->getClientOriginalExtension()),
                'mime_type' => $archivo->getMimeType(),
                'registros' => $registros,
                'filas_importadas' => count($registros),
                'total_monto_descuento' => round(
                    array_sum(array_column($registros, 'monto_descuento')),
                    6
                ),
                'total_tot_2' => round(
                    array_sum(array_column($registros, 'tot_2')),
                    6
                ),
                'total_comision' => round(
                    array_sum(array_column($registros, 'comision')),
                    6
                ),
            ];
        } finally {
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }
    }

    private function validarEncabezados(Worksheet $hoja): void
    {
        $cantidadColumnas = Coordinate::columnIndexFromString(
            $hoja->getHighestDataColumn(1)
        );

        if ($cantidadColumnas !== count(self::COLUMNAS)) {
            throw new InvalidArgumentException(
                'La estructura del Excel no coincide: se esperaban '
                . count(self::COLUMNAS) . " columnas y se encontraron {$cantidadColumnas}."
            );
        }

        $encabezados = [];

        for ($columna = 1; $columna <= count(self::COLUMNAS); $columna++) {
            $encabezados[] = strtoupper(trim(
                $this->textoCelda($hoja->getCell([$columna, 1]))
            ));
        }

        if ($encabezados === self::COLUMNAS) {
            return;
        }

        $diferencias = [];

        foreach (self::COLUMNAS as $indice => $esperada) {
            $recibida = $encabezados[$indice] ?? '';

            if ($recibida !== $esperada) {
                $diferencias[] = sprintf(
                    '%s: se esperaba "%s" y se recibió "%s"',
                    $this->letraColumna($indice + 1),
                    $esperada,
                    $recibida === '' ? 'vacío' : $recibida
                );
            }
        }

        throw new InvalidArgumentException(
            'La estructura del Excel no coincide. ' . implode('; ', $diferencias) . '.'
        );
    }

    private function leerRegistros(
        Worksheet $hoja,
        LoteMensual $lote
    ): array {
        $registros = [];
        $ultimaFila = $hoja->getHighestDataRow();
        $mesEsperado = $this->normalizarMes($lote->nombre_mes);

        if ($ultimaFila > 10000) {
            throw new InvalidArgumentException(
                'El archivo supera el límite de 10.000 filas permitido para esta carga.'
            );
        }

        for ($fila = 2; $fila <= $ultimaFila; $fila++) {
            $valores = [];

            for ($columna = 1; $columna <= count(self::COLUMNAS); $columna++) {
                $valores[$columna] = $this->textoCelda(
                    $hoja->getCell([$columna, $fila])
                );
            }

            if ($this->filaVacia($valores) || $this->filaTotales($valores)) {
                continue;
            }

            $gestion = $this->entero($valores[1]);
            $mes = trim($valores[2]);

            if ($gestion === null || $mes === '') {
                throw new InvalidArgumentException(
                    "La fila {$fila} no contiene GESTION y MES."
                );
            }

            if ($gestion !== $lote->gestion) {
                throw new InvalidArgumentException(
                    "La fila {$fila} pertenece a la gestión {$gestion}; "
                    . "el lote corresponde a {$lote->gestion}."
                );
            }

            if ($this->normalizarMes($mes) !== $mesEsperado) {
                throw new InvalidArgumentException(
                    "La fila {$fila} pertenece al mes {$mes}; "
                    . "el lote corresponde a {$lote->nombre_mes}."
                );
            }

            $montoDescuento = $this->decimal($valores[21]);
            $tot2 = $this->decimal($valores[22]);
            $comision = $this->decimal($valores[23]);

            if ($montoDescuento === null || $tot2 === null || $comision === null) {
                throw new InvalidArgumentException(
                    "La fila {$fila} contiene un importe no válido en "
                    . 'MONTO_DESCUENTO, TOT_2 o COMISION.'
                );
            }

            $registros[] = [
                'fila_origen' => $fila,
                'gestion' => $gestion,
                'mes' => LoteMensual::MESES[$lote->mes],
                'documento_respaldo' => $this->nuloSiVacio($valores[3]),
                'eit_codorg' => $this->nuloSiVacio($valores[4]),
                'organismos' => $this->nuloSiVacio($valores[5]),
                'eit_codrep' => $this->nuloSiVacio($valores[6]),
                'reparticion' => $this->nuloSiVacio($valores[7]),
                'grupo' => $this->nuloSiVacio($valores[8]),
                'descripcion_grupo' => $this->nuloSiVacio($valores[9]),
                'identificador_acreedor' => $this->nuloSiVacio($valores[10]),
                'acreedor' => $this->nuloSiVacio($valores[11]),
                'codigo_concepto' => $this->nuloSiVacio($valores[12]),
                'codigo_acreedor' => $this->nuloSiVacio($valores[13]),
                'cta_bancaria_acreedor' => $this->nuloSiVacio($valores[14]),
                'codigo_personal' => $this->normalizarCodigoPersonal(
                    $valores[15]
                ),
                'eit_item' => $this->nuloSiVacio($valores[16]),
                'carnet' => $this->nuloSiVacio($valores[17]),
                'grado' => $this->nuloSiVacio($valores[18]),
                'mension' => $this->nuloSiVacio($valores[19]),
                'nombres' => $this->nuloSiVacio($valores[20]),
                'monto_descuento' => $montoDescuento,
                'tot_2' => $tot2,
                'comision' => $comision,
                'estado' => 'IMPORTADO',
                'observacion' => null,
            ];
        }

        return $registros;
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

    private function filaVacia(array $valores): bool
    {
        return collect($valores)->every(
            fn (string $valor) => trim($valor) === ''
        );
    }

    private function filaTotales(array $valores): bool
    {
        for ($columna = 1; $columna <= 20; $columna++) {
            if (trim($valores[$columna]) !== '') {
                return false;
            }
        }

        return collect(array_slice($valores, 20, 3, true))->contains(
            fn (string $valor) => trim($valor) !== ''
        );
    }

    private function normalizarMes(string $mes): string
    {
        return Str::lower(Str::ascii(trim($mes)));
    }

    private function entero(string $valor): ?int
    {
        $valor = trim($valor);

        if ($valor === '' || ! preg_match('/^\d+$/', $valor)) {
            return null;
        }

        return (int) $valor;
    }

    private function decimal(string $valor): ?float
    {
        $valor = trim(str_replace(' ', '', $valor));

        if ($valor === '') {
            return null;
        }

        if (str_contains($valor, ',') && ! str_contains($valor, '.')) {
            $valor = str_replace(',', '.', $valor);
        } else {
            $valor = str_replace(',', '', $valor);
        }

        return is_numeric($valor) ? (float) $valor : null;
    }

    private function nuloSiVacio(string $valor): ?string
    {
        $valor = trim($valor);

        return $valor === '' ? null : $valor;
    }

    private function normalizarCodigoPersonal(string $valor): ?string
    {
        $valor = trim($valor);

        if ($valor === '') {
            return null;
        }

        if (preg_match('/^\d+(?:\.0+)?$/', $valor)) {
            $valor = preg_replace('/\.0+$/', '', $valor);
            $valor = ltrim((string) $valor, '0');

            return $valor === '' ? '0' : $valor;
        }

        return $valor;
    }

    private function letraColumna(int $numero): string
    {
        $letra = '';

        while ($numero > 0) {
            $numero--;
            $letra = chr(65 + ($numero % 26)) . $letra;
            $numero = intdiv($numero, 26);
        }

        return $letra;
    }
}

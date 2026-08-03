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

/**
 * Las planillas FVS comparten las 23 columnas y reglas de lectura de las
 * planillas ministeriales de préstamos.
 */
class FvsExcelImportService extends PrestamoExcelImportService
{
    private const COLUMNAS_IDENTIFICACION_ADICIONAL = [
        'DESTINO', 'PERSON', 'CARNET', 'GRADO', 'NOMBRES',
    ];

    public function leerAdicional(UploadedFile $archivo, LoteMensual $lote): array
    {
        return $this->leerAdicionalPorConcepto($archivo, $lote, 'FVS');
    }

    public function leerAdicionalAporte(UploadedFile $archivo, LoteMensual $lote): array
    {
        return $this->leerAdicionalPorConcepto($archivo, $lote, 'APORTE');
    }

    private function leerAdicionalPorConcepto(
        UploadedFile $archivo,
        LoteMensual $lote,
        string $concepto
    ): array {
        $this->validarNombreAdicional($archivo, $lote);

        try {
            $reader = IOFactory::createReaderForFile($archivo->getRealPath());
            $reader->setReadDataOnly(true);
            $libro = $reader->load($archivo->getRealPath());
        } catch (Throwable $exception) {
            throw new InvalidArgumentException(
                'No se pudo leer la planilla. Verifique que sea un Excel válido.',
                previous: $exception
            );
        }

        try {
            if ($libro->getSheetCount() < 1) {
                throw new InvalidArgumentException('El libro no contiene hojas.');
            }

            $hoja = $libro->getSheet(0);
            $columnas = $this->resolverColumnasAdicionales($hoja, $concepto);
            $resultado = $this->leerRegistrosAdicionales(
                $hoja,
                $columnas,
                $lote,
                $concepto
            );

            if ($resultado['registros'] === []) {
                throw new InvalidArgumentException(
                    "La planilla no contiene asociados con un importe {$concepto} mayor a cero."
                );
            }

            return [
                'hash_sha256' => hash_file('sha256', $archivo->getRealPath()),
                'nombre_original' => $archivo->getClientOriginalName(),
                'extension' => Str::lower($archivo->getClientOriginalExtension()),
                'mime_type' => $archivo->getMimeType() ?: $archivo->getClientMimeType(),
                'filas_importadas' => count($resultado['registros']),
                'filas_omitidas_sin_importe' => $resultado['omitidas'],
                'total_monto_descuento' => round(array_sum(array_column(
                    $resultado['registros'], 'monto_descuento'
                )), 6),
                'total_tot_2' => round(array_sum(array_column(
                    $resultado['registros'], 'tot_2'
                )), 6),
                'total_comision' => 0.0,
                'registros' => $resultado['registros'],
            ];
        } finally {
            $libro->disconnectWorksheets();
            unset($libro);
        }
    }

    private function validarNombreAdicional(UploadedFile $archivo, LoteMensual $lote): void
    {
        if (preg_match(
            '/\Aplanilla_mindef_(\d{2})_(\d{4})\.(xlsx|xls)\z/i',
            $archivo->getClientOriginalName(),
            $partes
        ) !== 1) {
            throw new InvalidArgumentException(
                'El nombre debe tener el formato planilla_mindef_MM_AAAA.xlsx.'
            );
        }

        if ((int) $partes[1] !== (int) $lote->mes
            || (int) $partes[2] !== (int) $lote->gestion) {
            throw new InvalidArgumentException(
                "El archivo corresponde a {$partes[1]}/{$partes[2]}; "
                ."el lote corresponde a {$lote->codigo_periodo}."
            );
        }
    }

    private function resolverColumnasAdicionales(Worksheet $hoja, string $concepto): array
    {
        $limite = min(Coordinate::columnIndexFromString($hoja->getHighestColumn()), 26);
        $columnas = [];

        for ($columna = 1; $columna <= $limite; $columna++) {
            $nombre = preg_replace('/\s+/', ' ', Str::upper(Str::ascii(
                trim($this->textoCeldaAdicional($hoja->getCell([$columna, 1])))
            ))) ?? '';

            if ($nombre !== '') {
                $columnas[$nombre] = $columna;
            }
        }

        $requeridas = [...self::COLUMNAS_IDENTIFICACION_ADICIONAL, $concepto];
        $faltantes = array_values(array_diff($requeridas, array_keys($columnas)));

        if ($faltantes !== []) {
            throw new InvalidArgumentException(
                'Faltan las columnas requeridas: '.implode(', ', $faltantes).'.'
            );
        }

        return $columnas;
    }

    private function leerRegistrosAdicionales(
        Worksheet $hoja,
        array $columnas,
        LoteMensual $lote,
        string $concepto
    ): array {
        $ultimaFila = $hoja->getHighestDataRow();

        if ($ultimaFila > 1000) {
            throw new InvalidArgumentException('La planilla supera el límite de 1.000 filas.');
        }

        $registros = [];
        $omitidas = 0;
        $codigos = [];

        for ($fila = 2; $fila <= $ultimaFila; $fila++) {
            $codigo = $this->normalizarCodigoAdicional(
                $this->valorAdicional($hoja, $columnas, 'PERSON', $fila)
            );
            $nombres = trim($this->valorAdicional($hoja, $columnas, 'NOMBRES', $fila));

            if ($codigo === '' || Str::upper(Str::ascii($nombres)) === 'TOTAL') {
                continue;
            }

            $importe = $this->decimalAdicional(
                $this->valorAdicional($hoja, $columnas, $concepto, $fila)
            );

            if ($importe === null) {
                throw new InvalidArgumentException(
                    "La fila {$fila} contiene un importe {$concepto} inválido."
                );
            }

            if ($importe < 0) {
                throw new InvalidArgumentException(
                    "La fila {$fila} contiene un importe {$concepto} negativo."
                );
            }

            if ($importe == 0.0) {
                $omitidas++;

                continue;
            }

            if (isset($codigos[$codigo])) {
                throw new InvalidArgumentException(
                    "La papeleta {$codigo} está repetida en las filas {$codigos[$codigo]} y {$fila}."
                );
            }

            $codigos[$codigo] = $fila;
            $registros[] = [
                'fila_origen' => $fila,
                'gestion' => (int) $lote->gestion,
                'mes' => LoteMensual::MESES[$lote->mes],
                'documento_respaldo' => null,
                'eit_codorg' => null,
                'organismos' => $this->nuloAdicional(
                    $this->valorAdicional($hoja, $columnas, 'DESTINO', $fila)
                ),
                'eit_codrep' => null,
                'reparticion' => null,
                'grupo' => $concepto,
                'descripcion_grupo' => $concepto,
                'identificador_acreedor' => null,
                'acreedor' => "{$concepto} CAS R.L.",
                'codigo_concepto' => null,
                'codigo_acreedor' => null,
                'cta_bancaria_acreedor' => null,
                'codigo_personal' => $codigo,
                'eit_item' => null,
                'carnet' => $this->nuloAdicional(
                    $this->valorAdicional($hoja, $columnas, 'CARNET', $fila)
                ),
                'grado' => $this->nuloAdicional(
                    $this->valorAdicional($hoja, $columnas, 'GRADO', $fila)
                ),
                'mension' => null,
                'nombres' => $this->nuloAdicional($nombres),
                'monto_descuento' => round($importe, 6),
                'tot_2' => round($importe, 6),
                'comision' => 0,
                'estado' => 'IMPORTADO',
                'observacion' => "Origen: planilla adicional MinDef (columna {$concepto})",
            ];
        }

        return ['registros' => $registros, 'omitidas' => $omitidas];
    }

    private function valorAdicional(Worksheet $hoja, array $columnas, string $nombre, int $fila): string
    {
        return $this->textoCeldaAdicional($hoja->getCell([$columnas[$nombre], $fila]));
    }

    private function textoCeldaAdicional(Cell $celda): string
    {
        $valor = $celda->getCalculatedValue();

        if ($valor === null) {
            return '';
        }

        if (is_float($valor)) {
            return rtrim(rtrim(sprintf('%.10F', $valor), '0'), '.');
        }

        return trim((string) $valor);
    }

    private function decimalAdicional(string $valor): ?float
    {
        $valor = trim(str_replace(' ', '', $valor));

        if ($valor === '') {
            return 0.0;
        }

        $valor = str_contains($valor, ',') && ! str_contains($valor, '.')
            ? str_replace(',', '.', $valor)
            : str_replace(',', '', $valor);

        return is_numeric($valor) ? (float) $valor : null;
    }

    private function normalizarCodigoAdicional(string $valor): string
    {
        $valor = trim($valor);

        if (preg_match('/^\d+(?:\.0+)?$/', $valor)) {
            $valor = preg_replace('/\.0+$/', '', $valor) ?? $valor;
            $valor = ltrim($valor, '0');

            return $valor === '' ? '0' : $valor;
        }

        return $valor;
    }

    private function nuloAdicional(string $valor): ?string
    {
        $valor = trim($valor);

        return $valor === '' ? null : $valor;
    }
}

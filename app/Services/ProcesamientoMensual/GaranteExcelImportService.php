<?php

namespace App\Services\ProcesamientoMensual;

use App\Models\LoteMensual;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

class GaranteExcelImportService
{
    private const MAX_FILAS = 5000;

    public function leer(UploadedFile $archivo, LoteMensual $lote): array
    {
        try {
            $reader = IOFactory::createReaderForFile($archivo->getRealPath());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($archivo->getRealPath());
        } catch (Throwable $exception) {
            throw new InvalidArgumentException(
                'No se pudo leer el libro de garantes. Verifique que sea un Excel válido.',
                previous: $exception
            );
        }

        try {
            if ($spreadsheet->getSheetCount() < 1) {
                throw new InvalidArgumentException(
                    'El libro de garantes no contiene hojas.'
                );
            }

            $hoja = $spreadsheet->getSheet(0);
            $filaEncabezado = $this->buscarFilaEncabezado($hoja);
            $registros = $this->leerRegistros($hoja, $filaEncabezado);

            if ($registros === []) {
                throw new InvalidArgumentException(
                    'El archivo no contiene descuentos a garantes para importar.'
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
                    array_sum(array_column($registros, 'monto_bs')),
                    6
                ),
                'gestion_lote' => $lote->gestion,
                'mes_lote' => $lote->mes,
            ];
        } finally {
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }
    }

    private function buscarFilaEncabezado(Worksheet $hoja): int
    {
        $ultimaFila = min(20, $hoja->getHighestDataRow());

        for ($fila = 1; $fila <= $ultimaFila; $fila++) {
            $encabezados = [];

            for ($columna = 1; $columna <= 13; $columna++) {
                $encabezados[] = $this->normalizarEncabezado(
                    $this->textoCelda($hoja->getCell([$columna, $fila]))
                );
            }

            if (in_array('CODIGO TITULAR', $encabezados, true)
                && in_array('CODIGO GARANTES', $encabezados, true)
                && in_array('MONTO BS', $encabezados, true)) {
                return $fila;
            }
        }

        throw new InvalidArgumentException(
            'No se encontró el encabezado esperado: CODIGO TITULAR, '
            . 'CODIGO GARANTES y MONTO BS.'
        );
    }

    private function leerRegistros(
        Worksheet $hoja,
        int $filaEncabezado
    ): array {
        $ultimaFila = $hoja->getHighestDataRow();

        if (($ultimaFila - $filaEncabezado) > self::MAX_FILAS) {
            throw new InvalidArgumentException(
                'El archivo de garantes supera el límite de '
                . number_format(self::MAX_FILAS)
                . ' filas.'
            );
        }

        $registros = [];

        for ($fila = $filaEncabezado + 1; $fila <= $ultimaFila; $fila++) {
            $valores = [];

            for ($columna = 1; $columna <= 13; $columna++) {
                $valores[$columna] = $this->textoCelda(
                    $hoja->getCell([$columna, $fila])
                );
            }

            if ($this->filaVacia($valores)) {
                continue;
            }

            $codigoTitular = $this->normalizarCodigo($valores[2]);
            $codigoGarante = $this->normalizarCodigo($valores[5]);
            $monto = $this->decimal($valores[9]);

            if ($codigoTitular === null
                || $codigoGarante === null
                || $monto === null
                || $monto <= 0) {
                throw new InvalidArgumentException(
                    "La fila {$fila} debe contener CODIGO TITULAR, "
                    . 'CODIGO GARANTES y un MONTO BS. mayor a cero.'
                );
            }

            $nombreGarante = trim(implode(' ', array_filter([
                trim($valores[6]),
                trim($valores[7]),
                trim($valores[8]),
            ])));
            $observaciones = array_values(array_filter(
                array_map(
                    fn (string $valor): string => trim($valor),
                    array_slice($valores, 9, 4, true)
                ),
                fn (string $valor): bool => $valor !== ''
            ));

            $registros[] = [
                'fila_origen' => $fila,
                'codigo_titular' => $codigoTitular,
                'nombre_titular' => $this->nuloSiVacio($valores[3]),
                'tipo_garante' => $this->nuloSiVacio($valores[4]),
                'codigo_garante' => $codigoGarante,
                'nombre_garante' =>
                    $nombreGarante === '' ? null : $nombreGarante,
                'monto_bs' => $monto,
                'observacion_excel' =>
                    $observaciones === []
                        ? null
                        : implode(' | ', $observaciones),
                'factor_conversion' => 6.96,
                'monto_aplicable' => round($monto / 6.96, 6),
                'monto_acumulado' => 0,
                'saldo_pendiente' => 0,
                'estado_conciliacion' => 'SIN_COMPARAR',
                'estado_aplicacion' => 'IMPORTADO',
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

    private function normalizarEncabezado(string $valor): string
    {
        $valor = strtoupper(trim($valor));
        $valor = str_replace(['.', ':'], '', $valor);

        return preg_replace('/\s+/', ' ', $valor) ?? '';
    }

    private function normalizarCodigo(string $valor): ?string
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

    private function filaVacia(array $valores): bool
    {
        foreach ($valores as $valor) {
            if (trim((string) $valor) !== '') {
                return false;
            }
        }

        return true;
    }
}

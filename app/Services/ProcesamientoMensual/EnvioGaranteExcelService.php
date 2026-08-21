<?php

namespace App\Services\ProcesamientoMensual;

use App\Models\EnvioMensual;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

class EnvioGaranteExcelService
{
    private const MAX_FILAS = 5000;

    /**
     * @return array{lineas:array<int,string>,cantidad:int,monto_total:string,hash_sha256:string}
     */
    public function leer(
        string $ruta,
        EnvioMensual $envio
    ): array {
        try {
            $reader = IOFactory::createReaderForFile($ruta);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($ruta);
        } catch (Throwable $exception) {
            throw new InvalidArgumentException(
                'No se pudo leer el Excel de garantes. Verifique que el archivo sea válido.',
                previous: $exception
            );
        }

        try {
            if ($spreadsheet->getSheetCount() < 1) {
                throw new InvalidArgumentException(
                    'El Excel de garantes no contiene hojas.'
                );
            }

            $hoja = $spreadsheet->getSheet(0);
            $filaEncabezado = $this->buscarFilaEncabezado($hoja);
            $this->validarPeriodo($hoja, $filaEncabezado, $envio);

            return $this->leerLineas($hoja, $filaEncabezado, $ruta);
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
                $encabezados[] = $this->normalizarTexto(
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
            .'CODIGO GARANTES y MONTO BS.'
        );
    }

    private function validarPeriodo(
        Worksheet $hoja,
        int $filaEncabezado,
        EnvioMensual $envio
    ): void {
        $titulos = [];

        for ($fila = 1; $fila < $filaEncabezado; $fila++) {
            for ($columna = 1; $columna <= 13; $columna++) {
                $valor = $this->textoCelda($hoja->getCell([$columna, $fila]));

                if ($valor !== '') {
                    $titulos[] = $this->normalizarTexto($valor);
                }
            }
        }

        $titulo = implode(' ', $titulos);
        $mes = $this->normalizarTexto($envio->nombre_mes);

        if (! str_contains($titulo, $mes)
            || ! str_contains($titulo, (string) $envio->gestion)) {
            throw new InvalidArgumentException(
                "El Excel de garantes no corresponde al periodo {$envio->periodo}."
            );
        }
    }

    /**
     * @return array{lineas:array<int,string>,cantidad:int,monto_total:string,hash_sha256:string}
     */
    private function leerLineas(
        Worksheet $hoja,
        int $filaEncabezado,
        string $ruta
    ): array {
        $ultimaFila = $hoja->getHighestDataRow();

        if (($ultimaFila - $filaEncabezado) > self::MAX_FILAS) {
            throw new InvalidArgumentException(
                'El Excel de garantes supera el límite de '
                .number_format(self::MAX_FILAS).' filas.'
            );
        }

        $lineas = [];
        $montoTotalCentavos = 0;

        for ($fila = $filaEncabezado + 1; $fila <= $ultimaFila; $fila++) {
            $codigoTitular = $this->textoCelda($hoja->getCell([2, $fila]));
            $codigoGarante = $this->textoCelda($hoja->getCell([5, $fila]));
            $montoTexto = $this->textoCelda($hoja->getCell([9, $fila]));

            if ($codigoTitular === '' && $codigoGarante === '' && $montoTexto === '') {
                continue;
            }

            $papeleta = $this->normalizarPapeleta($codigoGarante);
            $monto = $this->decimal($montoTexto);

            if ($codigoTitular === '' || $papeleta === null
                || $monto === null || $monto <= 0) {
                throw new InvalidArgumentException(
                    "La fila {$fila} debe contener CODIGO TITULAR, "
                    .'CODIGO GARANTES numérico y un MONTO BS. mayor a cero.'
                );
            }

            $centavos = (int) round($monto * 100);
            $montoTotalCentavos += $centavos;
            $lineas[] = implode('*', [
                '3',
                $papeleta,
                '171',
                '01',
                '1',
                'B',
                number_format($centavos / 100, 2, '.', ''),
                '1',
            ]);
        }

        if ($lineas === []) {
            throw new InvalidArgumentException(
                'El Excel no contiene descuentos a garantes.'
            );
        }

        return [
            'lineas' => $lineas,
            'cantidad' => count($lineas),
            'monto_total' => number_format(
                $montoTotalCentavos / 100,
                2,
                '.',
                ''
            ),
            'hash_sha256' => hash_file('sha256', $ruta),
        ];
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

    private function normalizarTexto(string $valor): string
    {
        $valor = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor) ?: $valor;
        $valor = strtoupper(trim(str_replace(['.', ':'], '', $valor)));

        return preg_replace('/\s+/', ' ', $valor) ?? '';
    }

    private function normalizarPapeleta(string $valor): ?string
    {
        $valor = trim($valor);

        if (! preg_match('/^\d+(?:\.0+)?$/', $valor)) {
            return null;
        }

        $valor = preg_replace('/\.0+$/', '', $valor) ?? $valor;
        $valor = ltrim($valor, '0');
        $valor = $valor === '' ? '0' : $valor;

        if (strlen($valor) > 8) {
            return null;
        }

        return str_pad($valor, 8, '0', STR_PAD_LEFT);
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
}

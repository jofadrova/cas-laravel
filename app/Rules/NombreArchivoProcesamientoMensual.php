<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class NombreArchivoProcesamientoMensual implements ValidationRule
{
    public const GRUPO_PRESTAMOS = 'PRESTAMOS';
    public const GRUPO_FVS = 'FVS';
    public const GRUPO_CERTIFICADOS = 'CERTIFICADOS';

    public function __construct(
        private readonly string $grupo
    ) {
    }

    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {
        if (! $value instanceof UploadedFile) {
            return;
        }

        $nombre = $value->getClientOriginalName();
        $patron = $this->patron();

        if ($patron === null || preg_match($patron, $nombre) !== 1) {
            $fail($this->mensaje());
        }
    }

    private function patron(): ?string
    {
        return match ($this->grupo) {
            self::GRUPO_PRESTAMOS =>
                '/\Apla\.prest\.cas_[A-Za-z0-9_-]+\.(?i:xlsx|xls)\z/D',
            self::GRUPO_FVS =>
                '/\Apla\.seg\.cas_[A-Za-z0-9_-]+\.(?i:xlsx|xls)\z/D',
            self::GRUPO_CERTIFICADOS =>
                '/\Apla\.apo\.[A-Za-z0-9_-]+\.(?i:xlsx|xls)\z/D',
            default => null,
        };
    }

    private function mensaje(): string
    {
        return match ($this->grupo) {
            self::GRUPO_PRESTAMOS =>
                'El nombre debe tener el formato pla.prest.cas_xxxxxx.xlsx '
                . 'o pla.prest.cas_xxxxxx.xls.',
            self::GRUPO_FVS =>
                'El nombre debe tener el formato pla.seg.cas_xxxxxx.xlsx '
                . 'o pla.seg.cas_xxxxxx.xls.',
            self::GRUPO_CERTIFICADOS =>
                'El nombre debe tener el formato pla.apo.xxxxxx.xlsx '
                . 'o pla.apo.xxxxxx.xls.',
            default => 'El nombre del archivo no corresponde al grupo seleccionado.',
        };
    }
}

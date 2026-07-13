<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGarantesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_garante1' => ['nullable', 'integer'],
            'id_garante2' => ['nullable', 'integer'],
            'observaciones' => ['required', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $prestamo = $this->route('prestamo');

            $g1 = $this->input('id_garante1');
            $g2 = $this->input('id_garante2');

            $g1Old = $prestamo->id_garante1;
            $g2Old = $prestamo->id_garante2;

            $idSocio = $prestamo->id_socio;

            // Debe cambiar al menos un garante

            if (empty($g1) && empty($g2)) {

                $validator->errors()->add(
                    'garantes',
                    'Debe seleccionar al menos un garante para cambiar.'
                );

                return;

            }

            // El solicitante no puede ser garante

            if ($g1 && $g1 == $idSocio) {

                $validator->errors()->add(
                    'id_garante1',
                    'El asociado solicitante no puede ser garante.'
                );

            }

            if ($g2 && $g2 == $idSocio) {

                $validator->errors()->add(
                    'id_garante2',
                    'El asociado solicitante no puede ser garante.'
                );

            }

            // No reutilizar garantes actuales

            if ($g1 && ($g1 == $g1Old || $g1 == $g2Old)) {

                $validator->errors()->add(
                    'id_garante1',
                    'Debe seleccionar un garante diferente al actual.'
                );

            }

            if ($g2 && ($g2 == $g2Old || $g2 == $g1Old)) {

                $validator->errors()->add(
                    'id_garante2',
                    'Debe seleccionar un garante diferente al actual.'
                );

            }
            if ($g1 && $g2 && $g1 == $g2) {
                $validator->errors()->add(
                    'id_garante2',
                    'Los nuevos garantes deben ser diferentes.'
                );
            }

            if (trim((string) $this->observaciones) === '') {
                $validator->errors()->add(
                    'observaciones',
                    'Debe registrar la justificación del cambio.'
                );
            }
        });
    }
}
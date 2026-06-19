<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSocioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            // DATOS PERSONALES
            'nombres'       => 'required|string|max:35',
            'paterno'       => 'required|string|max:25',
            'materno'       => 'nullable|string|max:25',

            'nro_doc'       => 'required|string|max:15',
            'expedido'      => 'required|string|max:2',

            'sexo'          => 'required|string|max:1',
            'estado_civil'  => 'required|string|max:2',

            'fecha_nac'     => 'required|date|before:today',

            // DATOS INSTITUCIONALES
            'es_asociado'   => 'required',

            'papeleta'      => 'required|string|max:10',
            'carnet_mil'    => 'required|string|max:15',
            'cossmil'       => 'required|string|max:12',

            'afil_mes'      => 'required|string|max:3',
            'afil_anio'     => 'required|integer|min:1950|max:' . date('Y'),

            'anio_prom'     => 'required|date',

            'id_escalafon'  => 'required|integer',
            'id_fuerza'     => 'required|integer',
            'id_arma'       => 'required|integer',
            'id_grado'      => 'required|integer',
            'id_diplomado'  => 'required|integer',

            'salario'       => 'required|numeric|min:0',

            // RESIDENCIA
            'departamento'  => 'required|string|max:10',
            'radicatoria'   => 'required|string|max:50',

            'zona'          => 'required|string|max:50',
            'calle'         => 'required|string|max:50',

            'nro'           => 'nullable|string|max:8',

            'telefono'      => 'required|string|max:15',

            'correo'        => 'nullable|email|max:255',

            'resolucion'    => 'required|integer',

            // DOCUMENTACION
            'solicitud'     => 'required',

            // FOTO
            'foto'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'es_asociado'       => 'required|in:SI,NO',
            'solicitud'         => 'required|in:FA,FI,OB',
            'afiliacion_afcoop' => 'nullable|boolean',
            'fotocopia_ci'      => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [

            'nombres.required'      => 'Debe ingresar los nombres.',
            'paterno.required'      => 'Debe ingresar el apellido paterno.',
            'nro_doc.required'      => 'Debe ingresar el número de CI.',

            'expedido.required'     => 'Seleccione el departamento de expedición.',
            'sexo.required'         => 'Seleccione el sexo.',
            'estado_civil.required' => 'Seleccione el estado civil.',

            'papeleta.required'     => 'Debe ingresar el número de papeleta.',
            'carnet_mil.required'   => 'Debe ingresar el carnet militar.',
            'cossmil.required'      => 'Debe ingresar el número COSSMIL.',

            'departamento.required' => 'Seleccione un departamento.',
            'telefono.required'     => 'Debe ingresar un teléfono.',

            'resolucion.required'   => 'Seleccione una resolución AFCOOP.',

            'foto.image'            => 'El archivo debe ser una imagen.',
            'foto.mimes'            => 'La fotografía debe ser JPG o PNG.',
            'foto.max'              => 'La fotografía no puede superar 2 MB.',
            'es_asociado.required' => 'Debe indicar si es asociado(a).',
            'es_asociado.in'       => 'El valor de asociado(a) es inválido.',

            'solicitud.required' => 'Debe seleccionar el formulario presentado.',
            'solicitud.in'       => 'El formulario seleccionado no es válido.',
            'afiliacion_afcoop.accepted' => 'Debe aceptar la afiliación a AFCOOP.',
            'fotocopia_ci.accepted' => 'Debe aceptar la fotocopia de CI.',
        ];
    }
}

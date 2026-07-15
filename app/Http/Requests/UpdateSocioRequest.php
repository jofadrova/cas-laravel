<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSocioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
     public function rules(): array
    {
        return [

            // DATOS PERSONALES
            'nombres'       => 'required|string|min:3',
            'paterno'       => 'required|string|min:3',
            'materno'       => 'nullable|string|min:3',

            'nro_doc'       => ['required', 'string', 'min:5', Rule::unique('socios', 'nro_doc')->ignore($this->route('socio'))],
            'expedido'      => 'required|string|max:2',

            'sexo'          => 'required|string|max:1',
            'estado_civil'  => 'required|string|max:2',

            'fecha_nac'     => 'required|date|before:today',

            // DATOS INSTITUCIONALES
            'papeleta'      => ['required', 'string', 'min:4', 'max:8', Rule::unique('socio_institucion', 'papeleta')->ignore($this->route('socio'), 'id_socio')],
            'carnet_mil'    => 'required|string|min:4|max:15',
            'cossmil'       => 'required|string|min:4|max:15',

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
            'ciudad'   => 'required|string|min:4',

            'zona'          => 'required|string|min:5',
            'calle'         => 'required|string|min:4',

            'nro'           => 'nullable|string|max:8',

            'telefono'      => 'required|string|min:8',

            'correo'        => 'nullable|email|max:100',

            'resolucion'    => 'required|integer',

            // DOCUMENTACION
           // FOTO
            'foto'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'solicitud'         => 'required|in:FA,FI,OB',
            'afiliacion_afcoop' => 'nullable|boolean',
            'fotocopia_ci'      => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [

            'nombres.required'      => 'Debe ingresar los nombres.',
            'nombres.min'           => 'Los nombres deben tener al menos 3 caracteres.',
            'paterno.required'      => 'Debe ingresar el apellido paterno.',
            'paterno.min'           => 'El apellido paterno debe tener al menos 3 caracteres.',
            'materno.min'           => 'El apellido materno debe tener al menos 3 caracteres.',
            'nro_doc.required'      => 'Debe ingresar el número de CI.',
            'nro_doc.min'           => 'El número de CI debe tener al menos 5 caracteres.',
            'nro_doc.unique'        => 'El número de CI ya está registrado.',
            'expedido.required'     => 'Seleccione el departamento de expedición.',
            'sexo.required'         => 'Seleccione el sexo.',
            'estado_civil.required' => 'Seleccione el estado civil.',
            'fecha_nac.required'    => 'Debe ingresar la fecha de nacimiento.',
            'fecha_nac.before'      => 'Debe ingresar una fecha válida.',

            'papeleta.required'     => 'Debe ingresar el número de papeleta.',
            'papeleta.min'          => 'El número de papeleta debe tener al menos 4 caracteres.',
            'papeleta.max'          => 'El número de papeleta no puede superar 8 caracteres.',
            'papeleta.unique'       => 'El número de papeleta ya está registrado.',
            'carnet_mil.required'   => 'Debe ingresar el carnet militar.',
            'carnet_mil.min'        => 'El carnet militar debe tener al menos 4 caracteres.',
            'carnet_mil.max'        => 'El carnet militar no puede superar 15 caracteres.',
            'cossmil.required'      => 'Debe ingresar el número COSSMIL.',
            'cossmil.min'           => 'El número COSSMIL debe tener al menos 4 caracteres.',
            'cossmil.max'           => 'El número COSSMIL no puede superar 15 caracteres.',
            'afil_mes.required'     => 'Debe ingresar el mes de afiliación.',
            'afil_anio.required'    => 'Debe ingresar el año de afiliación.',
            'anio_prom.required'    => 'Debe ingresar el año de promoción.',
            'anio_prom.date'        => 'Debe ingresar una fecha válida para el año de promoción.',
            'id_escalafon.required' => 'Seleccione el escalafón.',
            'id_fuerza.required'    => 'Seleccione la fuerza.',
            'id_arma.required'      => 'Seleccione el arma.',
            'id_grado.required'     => 'Seleccione el grado.',
            'id_diplomado.required' => 'Seleccione el diplomado.',
            'salario.required'      => 'Debe ingresar el salario.',
            'salario.min'           => 'El salario debe ser un número positivo.',

            'departamento.required' => 'Seleccione un departamento.',
            'ciudad.required'       => 'Debe ingresar el lugar/ciudad/region donde vive.',
            'ciudad.min'            => 'La ciudad debe tener al menos 4 caracteres.',
            'zona.required'       => 'Debe ingresar una zona.',
            'zona.min'              => 'La zona debe tener al menos 5 caracteres.',
            'calle.required'      => 'Debe ingresar una calle.',
            'calle.min'             => 'La calle debe tener al menos 4 caracteres.',

            'telefono.required'     => 'Debe ingresar un teléfono.',
            'telefono.min'          => 'El teléfono debe tener al menos 8 caracteres.',
            'correo.email'          => 'Debe ingresar un correo electrónico válido.',

            'resolucion.required'   => 'Seleccione una resolución AFCOOP.',

            'foto.image'            => 'El archivo debe ser una imagen.',
            'foto.mimes'            => 'La fotografía debe ser JPG o PNG.',
            'foto.max'              => 'La fotografía no puede superar 2 MB.',


            'solicitud.required' => 'Debe seleccionar el formulario presentado.',
            'solicitud.in'       => 'El formulario seleccionado no es válido.',
            'afiliacion_afcoop.accepted' => 'Debe aceptar la afiliación a AFCOOP.',
            'fotocopia_ci.accepted' => 'Debe aceptar la fotocopia de CI.',
        ];
    }
}

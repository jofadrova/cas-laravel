@props([
    'name',
    'label',
    'required' => false,
])

<div class="mb-3 position-relative scas-papeleta">

    <label class="form-label">

        {{ $label }}

    </label>

    <input
        type="hidden"
        name="{{ $name }}"
        class="scas-id">

    <input
    type="text"
    class="form-control scas-papeleta-input"
    autocomplete="off"
    spellcheck="false"
    placeholder="Ingrese la papeleta..."
    data-url="{{ route('socios.buscar') }}"
    {{ $required ? 'required' : '' }}>

    <div class="list-group position-absolute w-100 shadow scas-resultados"
         style="z-index:1055;display:none;">
    </div>

    <div class="form-text scas-nombre fw-semibold text-primary mt-1">

    </div>

</div>
@props(['name', 'label','required' => false])

<div class="mb-3 position-relative scas-papeleta">

    <label class="form-label">

        {{ $label }}

    </label>

     <input type="hidden" name="{{ $name }}" class="scas-id" value="{{ old($name) }}">
<small class="text-danger">
</small>
    <input
        type="text"
        class="form-control scas-papeleta-input @error($name) is-invalid @enderror"
        autocomplete="off"
        spellcheck="false"
        placeholder="Ingrese la papeleta..."
        data-url="{{ route('socios.buscar') }}"
        {{ $required ? 'required' : '' }}>
        @error($name)
        <div class="invalid-feedback d-block">
            {{ $message }}
        </div>
        @enderror
    <div class="list-group position-absolute w-100 shadow scas-resultados"
         style="z-index:1055;display:none;">
    </div>

    <div class="form-text scas-nombre fw-semibold text-primary mt-1">

    </div>
     

</div>
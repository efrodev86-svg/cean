@php
    $repoblar = (bool) old('nueva_sede');
@endphp

<div>
    <label class="admin-form-label" for="nueva-sede-nombre">Nombre <span class="text-cean-red">*</span></label>
    <input id="nueva-sede-nombre" name="nombre" type="text" class="admin-form-input" value="{{ $repoblar ? old('nombre') : '' }}" placeholder="Sede Centro" required>
    @if ($repoblar)@error('nombre')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
</div>
<div>
    <label class="admin-form-label" for="nueva-sede-clave">Clave (CCT) <span class="text-cean-red">*</span></label>
    <input id="nueva-sede-clave" name="clave" type="text" class="admin-form-input font-mono" value="{{ $repoblar ? old('clave') : '' }}" placeholder="22DNL0001P" required>
    @if ($repoblar)@error('clave')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
</div>
<div>
    <label class="admin-form-label" for="nueva-sede-escuela">Escuela (boleta)</label>
    <input id="nueva-sede-escuela" name="escuela" type="text" class="admin-form-input" value="{{ $repoblar ? old('escuela') : '' }}" placeholder="Vacío = usar valor por defecto">
</div>
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="admin-form-label" for="nueva-sede-director">Director</label>
        <input id="nueva-sede-director" name="director" type="text" class="admin-form-input" value="{{ $repoblar ? old('director') : '' }}" placeholder="Vacío = config">
    </div>
    <div>
        <label class="admin-form-label" for="nueva-sede-ciudad">Ciudad</label>
        <input id="nueva-sede-ciudad" name="ciudad" type="text" class="admin-form-input" value="{{ $repoblar ? old('ciudad') : '' }}" placeholder="Vacío = config">
    </div>
</div>
<div>
    <label class="admin-form-label" for="nueva-sede-logo">Logo (boleta)</label>
    <input
        id="nueva-sede-logo"
        name="logo"
        type="file"
        accept="image/jpeg,image/png,image/webp"
        class="admin-form-input file:mr-3 file:rounded-md file:border-0 file:bg-cean-cyan/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-cean-cyan hover:file:bg-cean-cyan/30"
    >
    <p class="mt-1 text-[11px] text-gray-500">JPG, PNG o WebP · máximo 2 MB. También puedes subirlo después desde Editar sede.</p>
    @if ($repoblar)@error('logo')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
</div>

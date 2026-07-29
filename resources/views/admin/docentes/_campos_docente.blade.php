@php
    $docente = $docente ?? null;
    $val = fn (string $campo) => old($campo, $docente?->{$campo});
@endphp

<div class="grid gap-4 sm:grid-cols-3">
    <div>
        <label class="admin-form-label" for="nombre">Nombre <span class="text-cean-red">*</span></label>
        <input id="nombre" name="nombre" type="text" class="admin-form-input" value="{{ $val('nombre') }}" required autofocus>
        @error('nombre')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="admin-form-label" for="primer_apellido">Primer apellido <span class="text-cean-red">*</span></label>
        <input id="primer_apellido" name="primer_apellido" type="text" class="admin-form-input" value="{{ $val('primer_apellido') }}" required>
        @error('primer_apellido')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="admin-form-label" for="segundo_apellido">Segundo apellido</label>
        <input id="segundo_apellido" name="segundo_apellido" type="text" class="admin-form-input" value="{{ $val('segundo_apellido') }}">
        @error('segundo_apellido')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
    </div>
</div>

<div>
    <label class="admin-form-label" for="email">Correo <span class="text-cean-red">*</span></label>
    <input id="email" name="email" type="email" class="admin-form-input" value="{{ $val('email') }}" required>
    @error('email')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
</div>

<div>
    <label class="admin-form-label" for="curp">CURP <span class="text-cean-red">*</span></label>
    <input id="curp" name="curp" type="text" class="admin-form-input font-mono uppercase" value="{{ $val('curp') }}" maxlength="18" required>
    @error('curp')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
</div>

@include('admin.docentes._grado_academico', ['gradoSeleccionado' => old('grado_academico_id', $docente?->grado_academico_id)])

<div class="grid gap-4 sm:grid-cols-3">
    <div>
        <label class="admin-form-label" for="tipo_contratacion">Tipo de contratación <span class="text-cean-red">*</span></label>
        <select id="tipo_contratacion" name="tipo_contratacion" class="admin-form-input" required>
            <option value="">Seleccionar…</option>
            @foreach (config('cean.tipos_contratacion_docente') as $clave => $etiqueta)
                <option value="{{ $clave }}" @selected((string) $val('tipo_contratacion') === (string) $clave)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        @error('tipo_contratacion')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="admin-form-label" for="clave_plaza">Clave de plaza</label>
        <input id="clave_plaza" name="clave_plaza" type="text" class="admin-form-input font-mono" value="{{ $val('clave_plaza') }}">
        @error('clave_plaza')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="admin-form-label" for="nombre_plaza">Nombre de plaza</label>
        <input id="nombre_plaza" name="nombre_plaza" type="text" class="admin-form-input" value="{{ $val('nombre_plaza') }}">
        @error('nombre_plaza')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
    </div>
</div>

<div>
    <label class="admin-form-label" for="celular">Celular <span class="text-cean-red">*</span></label>
    <input id="celular" name="celular" type="tel" class="admin-form-input" value="{{ $val('celular') }}" required>
    @error('celular')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
</div>

<label class="flex items-center gap-2 text-sm text-gray-300">
    <input type="hidden" name="activo" value="0">
    <input
        type="checkbox"
        name="activo"
        value="1"
        @checked(old('activo', $docente?->activo ?? true))
        class="rounded border-gray-600 bg-gray-800 text-cean-cyan"
    >
    Docente activo (puede acceder al portal docente)
</label>
@error('activo')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror

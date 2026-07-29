<div>
    <label class="admin-form-label" for="grado_academico_id">Grado académico</label>
    <select id="grado_academico_id" name="grado_academico_id" class="admin-form-input">
        <option value="">Sin grado</option>
        @foreach ($gradosAcademicos as $grado)
            <option value="{{ $grado->id }}" @selected((string) old('grado_academico_id', $gradoSeleccionado ?? '') === (string) $grado->id)>
                {{ $grado->abreviatura }}
            </option>
        @endforeach
    </select>
    <p class="mt-1 text-[11px] text-gray-500">
        Se antepone al nombre (ej. Mtro. Carlos Hernández).
        <a href="{{ route('admin.docentes.grados-academicos.index') }}" class="text-cean-cyan hover:underline">Gestionar grados</a>
    </p>
    @error('grado_academico_id')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
</div>

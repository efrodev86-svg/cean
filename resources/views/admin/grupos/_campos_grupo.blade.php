@php
    $grupo = $grupo ?? null;
@endphp

<div class="space-y-4">
    <div>
        <label class="admin-form-label" for="ciclo_escolar_id">Ciclo escolar <span class="text-cean-red">*</span></label>
        <select id="ciclo_escolar_id" name="ciclo_escolar_id" class="admin-form-input" required>
            <option value="">Selecciona un ciclo</option>
            @foreach ($ciclos as $ciclo)
                <option
                    value="{{ $ciclo->id }}"
                    @selected((string) old('ciclo_escolar_id', $grupo?->ciclo_escolar_id ?? $cicloSeleccionado?->id ?? '') === (string) $ciclo->id)
                >
                    {{ $ciclo->nombre }}{{ $ciclo->activo ? ' (activo)' : '' }}
                </option>
            @endforeach
        </select>
        @error('ciclo_escolar_id')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <label class="admin-form-label" for="semestre">Semestre <span class="text-cean-red">*</span></label>
            <select id="semestre" name="semestre" class="admin-form-input" required>
                <option value="">Selecciona</option>
                @foreach (range(1, 8) as $semestre)
                    <option
                        value="{{ $semestre }}"
                        @selected((string) old('semestre', $grupo?->semestre ?? '') === (string) $semestre)
                    >
                        {{ $semestre }}° semestre
                    </option>
                @endforeach
            </select>
            @error('semestre')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="admin-form-label" for="letra">Letra <span class="text-cean-red">*</span></label>
            <input
                id="letra"
                name="letra"
                type="text"
                maxlength="4"
                class="admin-form-input uppercase"
                value="{{ old('letra', $grupo?->letra ?? 'A') }}"
                placeholder="A"
                required
            >
            @error('letra')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="admin-form-label" for="licenciatura">Licenciatura <span class="text-cean-red">*</span></label>
            <select id="licenciatura" name="licenciatura" class="admin-form-input" required>
                <option value="">Selecciona</option>
                @foreach ($licenciaturasOpciones as $licenciatura)
                    <option
                        value="{{ $licenciatura->nombre_corto }}"
                        @selected(old('licenciatura', $grupo?->licenciatura ?? '') === $licenciatura->nombre_corto)
                    >
                        {{ $licenciatura->nombre_corto }}
                        @if (! empty($licenciatura->nombre) && $licenciatura->nombre !== $licenciatura->nombre_corto)
                            · {{ $licenciatura->nombre }}
                        @endif
                    </option>
                @endforeach
            </select>
            @error('licenciatura')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
        </div>
    </div>

    <p class="text-xs text-gray-500">
        El nombre del grupo se genera automáticamente, por ejemplo: <strong class="text-gray-400">2°-A · TELESECUNDARIA</strong>.
    </p>
</div>

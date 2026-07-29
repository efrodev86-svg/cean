@php
    $alumno = $alumno ?? null;
    $usuario = $alumno?->user;
    $pestañas = ['general', 'contacto', 'domicilio', 'salud'];
    $pestañaActiva = old('tab', request('tab', 'general'));
    $pestañaActiva = in_array($pestañaActiva, $pestañas, true) ? $pestañaActiva : 'general';
@endphp

<div
    x-data="{
        tab: @js($pestañaActiva),
        tipoIngreso: @js(old('tipo_ingreso', $alumno?->tipo_ingreso ?? \App\Support\AlumnoTipoIngreso::NUEVO)),
        curp: @js(old('curp', $alumno?->curp ?? '')),
        referenciaPago: @js(old('referencia_pago', $alumno?->referencia_pago ?? '')),
        tieneDiagnostico: @js(filter_var(old('tiene_diagnostico', $alumno?->tiene_diagnostico ?? false), FILTER_VALIDATE_BOOLEAN)),
        tieneDiscapacidad: @js(filter_var(old('tiene_discapacidad', $alumno?->tiene_discapacidad ?? false), FILTER_VALIDATE_BOOLEAN)),
        labora: @js(filter_var(old('labora', $alumno?->labora ?? false), FILTER_VALIDATE_BOOLEAN)),
        valores: {
            '0': 0, '1': 1, '2': 2, '3': 3, '4': 4, '5': 5, '6': 6, '7': 7, '8': 8, '9': 9,
            A: 1, B: 2, C: 3, D: 4, E: 5, F: 6, G: 7, H: 8, I: 9, J: 10,
            K: 11, L: 12, M: 13, N: 14, O: 15, P: 16, Q: 17, R: 18, S: 19, T: 20,
            U: 21, V: 22, W: 23, X: 24, Y: 25, Z: 26,
        },
        sumaDigitos(valor) {
            let suma = String(valor).split('').reduce((a, d) => a + Number(d), 0);
            return suma >= 10 ? this.sumaDigitos(suma) : suma;
        },
        conDigitoVerificador(base) {
            const invertida = base.split('').reverse().join('');
            const ponderaciones = [4, 8, 3];
            let suma = 0;
            let i = 0;
            invertida.split('').forEach((char, key) => {
                if (key % 3 === 0) i = 0;
                const producto = (this.valores[char] ?? 0) * ponderaciones[i];
                suma += producto >= 10 ? this.sumaDigitos(producto) : producto;
                i++;
            });
            let digito = 10 - (suma % 10);
            if (digito > 9) digito = 0;
            return base + digito;
        },
        actualizarReferencia() {
            this.curp = String(this.curp || '').toUpperCase().replace(/\s+/g, '');
            const base = this.curp.slice(0, 10);
            this.referenciaPago = base.length === 10 ? this.conDigitoVerificador(base) : '';
        },
    }"
    class="space-y-4"
>
    <input type="hidden" name="tab" :value="tab">

    <div class="flex flex-wrap gap-2 border-b border-gray-800 pb-3">
        @foreach ([
            'general' => 'General',
            'contacto' => 'Contacto y acceso',
            'domicilio' => 'Domicilio',
            'salud' => 'Salud y laboral',
        ] as $clave => $etiqueta)
            <button
                type="button"
                class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
                :class="tab === '{{ $clave }}' ? 'bg-cean-cyan/20 text-cean-cyan' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200'"
                @click="tab = '{{ $clave }}'"
            >
                {{ $etiqueta }}
            </button>
        @endforeach
    </div>

    <div x-show="tab === 'general'" x-cloak class="space-y-4">
        <div>
            <label class="admin-form-label" for="grupo_id">Grupo escolar <span class="text-cean-red">*</span></label>
            <select id="grupo_id" name="grupo_id" class="admin-form-input" required>
                <option value="">Selecciona un grupo</option>
                @foreach ($grupos as $grupo)
                    <option
                        value="{{ $grupo->id }}"
                        @selected((string) old('grupo_id', $alumno?->grupo_id ?? $grupoSeleccionado ?? '') === (string) $grupo->id)
                    >
                        {{ $grupo->etiqueta() }}
                        @if ($grupo->relationLoaded('cicloEscolar'))
                            · {{ $grupo->cicloEscolar->nombre }}
                        @endif
                    </option>
                @endforeach
            </select>
            @error('grupo_id')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="admin-form-label" for="curp">CURP <span class="text-cean-red">*</span></label>
                <input
                    id="curp"
                    name="curp"
                    type="text"
                    maxlength="18"
                    class="admin-form-input font-mono uppercase"
                    x-model="curp"
                    @input="actualizarReferencia"
                    required
                >
                @error('curp')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="admin-form-label" for="referencia_pago">Referencia de pago</label>
                <input
                    id="referencia_pago"
                    name="referencia_pago"
                    type="text"
                    class="admin-form-input font-mono bg-gray-900/60"
                    x-model="referenciaPago"
                    readonly
                    tabindex="-1"
                >
                <p class="mt-1.5 text-xs text-gray-500">Se genera automáticamente con los 10 primeros caracteres de la CURP y su dígito verificador.</p>
                @error('referencia_pago')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="admin-form-label" for="matricula">Matrícula</label>
                <input id="matricula" name="matricula" type="text" class="admin-form-input font-mono" value="{{ old('matricula', $alumno?->matricula) }}">
                @error('matricula')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="admin-form-label" for="nss">Número de seguro social</label>
                <input id="nss" name="nss" type="text" class="admin-form-input font-mono" value="{{ old('nss', $alumno?->nss) }}">
                @error('nss')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="admin-form-label" for="nombres">Nombre(s) <span class="text-cean-red">*</span></label>
                <input id="nombres" name="nombres" type="text" class="admin-form-input" value="{{ old('nombres', $alumno?->nombres) }}" required>
                @error('nombres')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="admin-form-label" for="fecha_nacimiento">Fecha de nacimiento <span class="text-cean-red">*</span></label>
                <input id="fecha_nacimiento" name="fecha_nacimiento" type="date" class="admin-form-input" value="{{ old('fecha_nacimiento', optional($alumno?->fecha_nacimiento)->format('Y-m-d')) }}" required>
                @error('fecha_nacimiento')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="admin-form-label" for="apellido_paterno">Primer apellido <span class="text-cean-red">*</span></label>
                <input id="apellido_paterno" name="apellido_paterno" type="text" class="admin-form-input" value="{{ old('apellido_paterno', $alumno?->apellido_paterno) }}" required>
                @error('apellido_paterno')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="admin-form-label" for="apellido_materno">Segundo apellido</label>
                <input id="apellido_materno" name="apellido_materno" type="text" class="admin-form-input" value="{{ old('apellido_materno', $alumno?->apellido_materno) }}">
                @error('apellido_materno')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="admin-form-label" for="estatus">Estatus <span class="text-cean-red">*</span></label>
                <select id="estatus" name="estatus" class="admin-form-input" required>
                    @foreach (\App\Support\AlumnoEstatus::opciones() as $valor => $etiqueta)
                        <option
                            value="{{ $valor }}"
                            @selected(old('estatus', $alumno?->estatus ?? \App\Support\AlumnoEstatus::REGULAR) === $valor)
                        >
                            {{ $etiqueta }}
                        </option>
                    @endforeach
                </select>
                @error('estatus')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="admin-form-label" for="tipo_ingreso">Tipo de ingreso <span class="text-cean-red">*</span></label>
                <select
                    id="tipo_ingreso"
                    name="tipo_ingreso"
                    class="admin-form-input"
                    required
                    x-model="tipoIngreso"
                >
                    @foreach (\App\Support\AlumnoTipoIngreso::opciones() as $valor => $etiqueta)
                        <option
                            value="{{ $valor }}"
                            @selected(old('tipo_ingreso', $alumno?->tipo_ingreso ?? \App\Support\AlumnoTipoIngreso::NUEVO) === $valor)
                        >{{ $etiqueta }}</option>
                    @endforeach
                </select>
                @error('tipo_ingreso')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
        </div>

        <div x-show="tipoIngreso === '{{ \App\Support\AlumnoTipoIngreso::TRASLADO }}'" x-cloak class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="admin-form-label" for="entidad_procedencia">Entidad de procedencia <span class="text-cean-red">*</span></label>
                <input
                    id="entidad_procedencia"
                    name="entidad_procedencia"
                    type="text"
                    class="admin-form-input"
                    placeholder="Ej. Escuela Normal de Guanajuato"
                    value="{{ old('entidad_procedencia', $alumno?->entidad_procedencia) }}"
                    :required="tipoIngreso === '{{ \App\Support\AlumnoTipoIngreso::TRASLADO }}'"
                >
                @error('entidad_procedencia')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="admin-form-label" for="ciudad_procedencia">Ciudad de procedencia <span class="text-cean-red">*</span></label>
                <input
                    id="ciudad_procedencia"
                    name="ciudad_procedencia"
                    type="text"
                    class="admin-form-input"
                    placeholder="Ej. León, Gto."
                    value="{{ old('ciudad_procedencia', $alumno?->ciudad_procedencia) }}"
                    :required="tipoIngreso === '{{ \App\Support\AlumnoTipoIngreso::TRASLADO }}'"
                >
                @error('ciudad_procedencia')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="admin-form-label" for="asignatura_adeuda">Asignatura que adeuda</label>
            <textarea id="asignatura_adeuda" name="asignatura_adeuda" rows="3" class="admin-form-input">{{ old('asignatura_adeuda', $alumno?->asignatura_adeuda) }}</textarea>
            @error('asignatura_adeuda')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
        </div>
    </div>

    <div x-show="tab === 'contacto'" x-cloak class="space-y-4">
        <p class="text-xs text-gray-500">El acceso al portal usa el correo institucional; si no existe, el personal.</p>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="admin-form-label" for="email_institucional">Correo institucional</label>
                <input id="email_institucional" name="email_institucional" type="email" class="admin-form-input" value="{{ old('email_institucional', $alumno?->email_institucional) }}">
                @error('email_institucional')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="admin-form-label" for="email_personal">Correo personal</label>
                <input id="email_personal" name="email_personal" type="email" class="admin-form-input" value="{{ old('email_personal', $alumno?->email_personal) }}">
                @error('email_personal')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="admin-form-label" for="celular">Celular</label>
                <input id="celular" name="celular" type="text" class="admin-form-input" value="{{ old('celular', $alumno?->celular) }}">
                @error('celular')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="admin-form-label" for="telefono_emergencia">Teléfono de emergencia</label>
                <input id="telefono_emergencia" name="telefono_emergencia" type="text" class="admin-form-input" value="{{ old('telefono_emergencia', $alumno?->telefono_emergencia) }}">
                @error('telefono_emergencia')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="border-t border-gray-800 pt-4">
            <h3 class="text-sm font-semibold text-white">Acceso al portal</h3>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="admin-form-label" for="password">Contraseña</label>
                <input id="password" name="password" type="password" class="admin-form-input" autocomplete="new-password" placeholder="{{ $alumno ? 'Dejar vacío para no cambiar' : 'Opcional' }}">
                @error('password')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="admin-form-label" for="password_confirmation">Confirmar contraseña</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="admin-form-input" autocomplete="new-password">
            </div>
        </div>

        @if (! $alumno)
            <p class="text-[11px] text-gray-500">Si dejas la contraseña vacía se usará la matrícula como contraseña inicial.</p>
        @endif

        <div>
            <input type="hidden" name="activo" value="0">
            <label class="flex items-center gap-2 text-sm text-gray-300">
                <input type="checkbox" name="activo" value="1" @checked(old('activo', $usuario?->activo ?? true)) class="rounded border-gray-600 bg-gray-800 text-cean-cyan">
                Cuenta activa (puede acceder al portal alumno)
            </label>
            @error('activo')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
        </div>
    </div>

    <div x-show="tab === 'domicilio'" x-cloak class="space-y-4">
        <div>
            <label class="admin-form-label" for="domicilio">Domicilio</label>
            <input id="domicilio" name="domicilio" type="text" class="admin-form-input" value="{{ old('domicilio', $alumno?->domicilio) }}">
            @error('domicilio')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="admin-form-label" for="colonia">Colonia</label>
                <input id="colonia" name="colonia" type="text" class="admin-form-input" value="{{ old('colonia', $alumno?->colonia) }}">
                @error('colonia')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="admin-form-label" for="codigo_postal">Código postal</label>
                <input id="codigo_postal" name="codigo_postal" type="text" class="admin-form-input" value="{{ old('codigo_postal', $alumno?->codigo_postal) }}">
                @error('codigo_postal')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="admin-form-label" for="estado">Estado</label>
                <input id="estado" name="estado" type="text" class="admin-form-input" value="{{ old('estado', $alumno?->estado) }}">
                @error('estado')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="admin-form-label" for="municipio">Municipio</label>
                <input id="municipio" name="municipio" type="text" class="admin-form-input" value="{{ old('municipio', $alumno?->municipio) }}">
                @error('municipio')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div x-show="tab === 'salud'" x-cloak class="space-y-4">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-3">
                <input type="hidden" name="tiene_diagnostico" value="0">
                <div>
                    <label class="admin-form-label" for="diagnostico_detalle">Presenta diagnóstico psicológico, médico o cognitivo</label>
                    <label class="mt-1.5 flex items-start gap-2 text-sm text-gray-300">
                        <input
                            type="checkbox"
                            name="tiene_diagnostico"
                            value="1"
                            x-model="tieneDiagnostico"
                            class="mt-0.5 rounded border-gray-600 bg-gray-800 text-cean-cyan"
                        >
                        <span>Especifique diagnóstico</span>
                    </label>
                    <textarea
                        id="diagnostico_detalle"
                        name="diagnostico_detalle"
                        rows="2"
                        class="admin-form-input"
                        :class="! tieneDiagnostico && 'opacity-50'"
                        :disabled="! tieneDiagnostico"
                        :required="tieneDiagnostico"
                    >{{ old('diagnostico_detalle', $alumno?->diagnostico_detalle) }}</textarea>
                    @error('diagnostico_detalle')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="space-y-3">
                <input type="hidden" name="tiene_discapacidad" value="0">
                <div>
                    <label class="admin-form-label" for="discapacidad_detalle">Especifique discapacidad</label>
                    <label class="mt-1.5 flex items-start gap-2 text-sm text-gray-300">
                        <input
                            type="checkbox"
                            name="tiene_discapacidad"
                            value="1"
                            x-model="tieneDiscapacidad"
                            class="mt-0.5 rounded border-gray-600 bg-gray-800 text-cean-cyan"
                        >
                        <span>Presenta discapacidad</span>
                    </label>
                    <textarea
                        id="discapacidad_detalle"
                        name="discapacidad_detalle"
                        rows="2"
                        class="admin-form-input"
                        :class="! tieneDiscapacidad && 'opacity-50'"
                        :disabled="! tieneDiscapacidad"
                        :required="tieneDiscapacidad"
                    >{{ old('discapacidad_detalle', $alumno?->discapacidad_detalle) }}</textarea>
                    @error('discapacidad_detalle')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="admin-form-label" for="estado_civil">Estado civil</label>
                <select id="estado_civil" name="estado_civil" class="admin-form-input">
                    <option value="">Selecciona una opción</option>
                    @foreach (\App\Support\AlumnoEstadoCivil::opciones() as $valor => $etiqueta)
                        <option
                            value="{{ $valor }}"
                            @selected(\App\Support\AlumnoEstadoCivil::normalizar(old('estado_civil', $alumno?->estado_civil)) === $valor)
                        >
                            {{ $etiqueta }}
                        </option>
                    @endforeach
                </select>
                @error('estado_civil')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-3">
                <input type="hidden" name="labora" value="0">
                <div>
                    <label class="admin-form-label" for="lugar_trabajo">¿Dónde labora?</label>
                    <label class="mt-1.5 flex items-start gap-2 text-sm text-gray-300">
                        <input
                            type="checkbox"
                            name="labora"
                            value="1"
                            x-model="labora"
                            class="mt-0.5 rounded border-gray-600 bg-gray-800 text-cean-cyan"
                        >
                        <span>Labora actualmente</span>
                    </label>
                    <input
                        id="lugar_trabajo"
                        name="lugar_trabajo"
                        type="text"
                        class="admin-form-input"
                        :class="! labora && 'opacity-50'"
                        :disabled="! labora"
                        :required="labora"
                        value="{{ old('lugar_trabajo', $alumno?->lugar_trabajo) }}"
                    >
                    @error('lugar_trabajo')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

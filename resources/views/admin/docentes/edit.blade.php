@php
    $estudioEditandoInicial = null;

    if (old('estudio_edit_id')) {
        $estudioEditandoInicial = [
            'id' => (int) old('estudio_edit_id'),
            'descripcion' => old('descripcion'),
            'documento_probatorio' => old('documento_probatorio'),
            'fecha' => old('fecha'),
        ];
    }
@endphp

<x-admin-layout title="Editar docente" breadcrumb="Editar docente">
    <div
        class="mx-auto max-w-3xl space-y-6"
        x-data="{
            modalEstudios: @js((bool) session('estudios_modal') || (bool) old('estudio_edit_id') || (session('estudios_modal') && $errors->any())),
            estudios: @js($estudiosCursados),
            estudioEditando: @js($estudioEditandoInicial),
            abrirEstudios() {
                this.estudioEditando = null;
                this.modalEstudios = true;
            },
            formatearFecha(fecha) {
                if (! fecha) {
                    return '—';
                }

                const [anio, mes, dia] = fecha.split('-');

                return `${dia}/${mes}/${anio}`;
            },
        }"
        @keydown.escape.window="modalEstudios = false; estudioEditando = null"
    >
        @if (session('success'))
            <div class="admin-alert-success">{{ session('success') }}</div>
        @endif

        <div>
            <a href="{{ route('admin.docentes.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-400 transition hover:text-cean-cyan">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                Volver a docentes
            </a>
        </div>

        <div class="admin-panel">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-white">Editar docente</h2>
                <p class="mt-1 text-sm text-gray-400">{{ $docente->nombreConGrado() }}</p>
            </div>

            <form method="POST" action="{{ route('admin.docentes.update', $docente) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                @include('admin.docentes._campos_docente', ['docente' => $docente])

                @if ($esAdminGlobal)
                    @php
                        $sedesSeleccionadas = collect(old('sedes', $docente->sedes->pluck('id')->all()))
                            ->map(fn ($id) => (string) $id)
                            ->all();
                    @endphp
                    <div>
                        <label class="admin-form-label">Sedes donde imparte <span class="text-cean-red">*</span></label>
                        <div class="mt-1 grid gap-2 sm:grid-cols-2">
                            @foreach ($sedes as $sede)
                                <label class="flex items-center gap-2 rounded-lg border border-gray-700 px-3 py-2 text-sm text-gray-300">
                                    <input type="checkbox" name="sedes[]" value="{{ $sede->id }}"
                                        @checked(in_array((string) $sede->id, $sedesSeleccionadas))
                                        class="rounded border-gray-600 bg-gray-800 text-cean-cyan">
                                    {{ $sede->nombre }}
                                </label>
                            @endforeach
                        </div>
                        @error('sedes')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
                    </div>
                @else
                    <p class="rounded-lg border border-gray-700 bg-gray-800/40 px-3 py-2 text-xs text-gray-400">
                        Sede asignada: <strong class="text-gray-200">{{ $sedes->first()?->nombre }}</strong>
                    </p>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="admin-form-label" for="password">Nueva contraseña</label>
                        <input id="password" name="password" type="password" class="admin-form-input" autocomplete="new-password" placeholder="Dejar vacío para no cambiar">
                        @error('password')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="admin-form-label" for="password_confirmation">Confirmar contraseña</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="admin-form-input" autocomplete="new-password">
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-800 pt-4">
                    <a href="{{ route('admin.docentes.index') }}" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-cean-primary">Guardar cambios</button>
                </div>
            </form>

            <section class="mt-6 space-y-3 border-t border-gray-800 pt-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-white">Historial de asignaturas</h3>
                        <p class="mt-1 text-xs text-gray-400">
                            Materias asignadas por grupo y ciclo escolar. Se gestionan desde Grupos → Asignaciones.
                        </p>
                    </div>
                </div>

                @if ($historialAsignaciones->isEmpty())
                    <p class="text-sm text-gray-500">Sin asignaturas registradas todavía.</p>
                @else
                    <div class="space-y-4">
                        @foreach ($historialAsignaciones as $bloque)
                            <div class="rounded-xl border border-gray-800 bg-gray-900/40 p-4">
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <h4 class="text-sm font-medium text-white">Ciclo {{ $bloque['ciclo'] }}</h4>
                                    @if ($bloque['activo'])
                                        <span class="rounded-full bg-green-900/40 px-2 py-0.5 text-[10px] font-semibold uppercase text-green-300">Activo</span>
                                    @endif
                                    <span class="text-xs text-gray-500">{{ $bloque['asignaciones']->count() }} materia(s)</span>
                                </div>
                                <div class="overflow-x-auto rounded-lg border border-gray-800">
                                    <table class="min-w-full divide-y divide-gray-800 text-sm">
                                        <thead class="bg-gray-800/50">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">Materia</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">Clave</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">Grupo</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">Sede</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-800">
                                            @foreach ($bloque['asignaciones'] as $asignacion)
                                                <tr>
                                                    <td class="px-3 py-2 text-gray-200">{{ $asignacion->materia?->nombre ?? '—' }}</td>
                                                    <td class="px-3 py-2 font-mono text-xs text-cean-cyan">{{ $asignacion->materia?->clave ?? '—' }}</td>
                                                    <td class="px-3 py-2 text-gray-400">{{ $asignacion->grupo?->etiqueta() ?? '—' }}</td>
                                                    <td class="px-3 py-2 text-gray-400">{{ $asignacion->grupo?->sede?->nombre ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="mt-6 space-y-3 border-t border-gray-800 pt-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-white">Estudios cursados</h3>
                        <p class="mt-1 text-xs text-gray-400">
                            Formación académica personal del docente. No depende de las sedes donde imparte.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 rounded-lg border border-gray-600 px-2.5 py-1.5 text-xs font-medium text-gray-300 transition hover:border-cean-cyan/50 hover:text-cean-cyan"
                        @click="abrirEstudios()"
                    >
                        Gestionar estudios
                    </button>
                </div>
                @if (count($estudiosCursados) === 0)
                    <p class="text-sm text-gray-500">Sin estudios registrados.</p>
                @else
                    <p class="text-sm text-gray-400">{{ count($estudiosCursados) }} estudio(s) registrado(s).</p>
                @endif
            </section>

            <form method="POST" action="{{ route('admin.docentes.destroy', $docente) }}" class="mt-4 border-t border-gray-800 pt-4" onsubmit="return confirm('¿Confirmas esta acción?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-rose-400 hover:underline">
                    {{ $esAdminGlobal ? 'Eliminar docente' : 'Quitar de mi sede' }}
                </button>
            </form>
        </div>

        @include('admin.docentes._modal_estudios_cursados')
    </div>
</x-admin-layout>

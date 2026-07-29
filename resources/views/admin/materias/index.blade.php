@php
    $materiaEditandoInicial = null;
    $modalEditarMateriaAbierto = false;

    if (old('_method') === 'patch' && old('materia_edit_id') && ! $errors->has('nombre_corto')) {
        $materiaEditandoInicial = [
            'id' => (int) old('materia_edit_id'),
            'licenciatura_id' => old('licenciatura_id'),
            'clave' => old('clave'),
            'nombre' => old('nombre'),
            'semestre' => old('semestre'),
            'orden' => old('orden'),
            'creditos' => old('creditos'),
        ];
        $modalEditarMateriaAbierto = $errors->has('clave')
            || $errors->has('nombre')
            || $errors->has('semestre')
            || $errors->has('orden')
            || $errors->has('creditos')
            || $errors->has('licenciatura_id');
    }

    $modalAgregarMateriaAbierto = old('licenciatura_id')
        && ! $errors->has('nombre_corto')
        && old('_method') !== 'patch'
        && ($errors->has('clave') || $errors->has('nombre') || $errors->has('semestre') || $errors->has('orden') || $errors->has('creditos'));
@endphp

<x-admin-layout title="Materias y Carreras" breadcrumb="Materias">
    <div
        class="mx-auto max-w-7xl space-y-6"
        x-data="{
            modalLicenciatura: @js(! old('licenciatura_id') && old('_method') !== 'patch' && ($errors->has('nombre_corto') || $errors->has('clave_dgp') || $errors->has('nombre') || $errors->has('plan_nombre') || $errors->has('anio_plan'))),
            modalEditarLicenciatura: @js(old('_method') === 'patch' && ($errors->has('nombre_corto') || $errors->has('clave_dgp') || $errors->has('nombre') || $errors->has('anio_plan') || $errors->has('plan_nombre'))),
            modalAgregarMateria: @js($modalAgregarMateriaAbierto),
            modalEditarMateria: @js($modalEditarMateriaAbierto),
            modalConfirmarEliminarMateria: false,
            mensajeOrden: '',
            errorOrden: '',
            materiaEditando: @js($materiaEditandoInicial),
            abrirEditarMateria(materia) {
                this.materiaEditando = materia;
                this.modalEditarMateria = true;
            },
            cerrarEditarMateria() {
                this.modalEditarMateria = false;
                this.modalConfirmarEliminarMateria = false;
            }
        }"
        @keydown.escape.window="modalConfirmarEliminarMateria ? (modalConfirmarEliminarMateria = false) : (modalLicenciatura = false; modalEditarLicenciatura = false; modalAgregarMateria = false; cerrarEditarMateria())"
        @materias-orden-guardado.window="mensajeOrden = 'Orden actualizado.'; errorOrden = ''; setTimeout(() => mensajeOrden = '', 3000)"
        @materias-orden-error.window="errorOrden = $event.detail.message; mensajeOrden = ''; setTimeout(() => errorOrden = '', 5000)"
    >
        <div x-show="mensajeOrden" x-cloak class="admin-alert-success" x-text="mensajeOrden"></div>
        <div x-show="errorOrden" x-cloak class="admin-alert-error"><span x-text="errorOrden"></span></div>

        @if (session('success'))
            <div class="admin-alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="admin-alert-error">{{ session('error') }}</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Licenciaturas --}}
            <section class="admin-panel lg:col-span-4">
                <h2 class="text-lg font-semibold text-white">Licenciaturas</h2>
                <div class="mb-4 mt-3">
                    <button
                        type="button"
                        class="btn-cean-primary w-full justify-center text-sm"
                        @click="modalLicenciatura = true"
                    >
                        Registrar
                    </button>
                </div>

                @if ($licenciaturas->isEmpty())
                    <p class="text-sm text-gray-400">No hay licenciaturas registradas. Usa el botón de arriba para agregar una.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($licenciaturas as $licenciatura)
                            @php
                                $activa = $licenciaturaSeleccionada?->id === $licenciatura->id;
                            @endphp
                            <li class="{{ $activa ? 'licenciatura-card licenciatura-card-active' : 'licenciatura-card' }}">
                                <a
                                    href="{{ route('admin.materias', array_filter(['licenciatura' => $licenciatura->id, 'semestre' => $filtros['semestre'], 'q' => $filtros['q']])) }}"
                                    class="block p-4"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="font-semibold text-white">{{ $licenciatura->nombre_corto }}</p>
                                            <p class="mt-1 text-xs text-gray-400">{{ $licenciatura->nombre }}</p>
                                            @if ($licenciatura->anio_plan)
                                                <p class="mt-1 font-mono text-[10px] text-gray-500">Plan {{ $licenciatura->anio_plan }} · {{ $licenciatura->materias_count }} materias</p>
                                            @endif
                                        </div>
                                        <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $licenciatura->activa ? 'bg-green-900/40 text-green-300' : 'bg-gray-700 text-gray-400' }}">
                                            {{ $licenciatura->activa ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- Materias --}}
            <section class="admin-panel lg:col-span-8">
                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-white">Materias del plan</h2>
                        @if ($licenciaturaSeleccionada)
                            <p class="text-sm text-gray-400">{{ $licenciaturaSeleccionada->nombre_corto }} — {{ $materias->count() }} en listado</p>
                        @endif
                    </div>
                    @if ($licenciaturaSeleccionada)
                        <button
                            type="button"
                            class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-200 transition hover:border-cean-cyan/50 hover:text-cean-cyan"
                            @click="modalEditarLicenciatura = true"
                        >
                            Editar Carrera
                        </button>
                    @endif
                </div>

                @if ($licenciaturaSeleccionada)
                    <hr class="mb-4 border-gray-700">

                    <div class="mb-4">
                        <button
                            type="button"
                            class="btn-cean-primary w-full justify-center text-sm sm:w-auto"
                            @click="modalAgregarMateria = true"
                        >
                            Agregar materia
                        </button>
                    </div>

                    <form method="GET" action="{{ route('admin.materias') }}" class="mb-4 grid gap-3 sm:grid-cols-2">
                        <input type="hidden" name="licenciatura" value="{{ $licenciaturaSeleccionada->id }}">
                        <div>
                            <label class="admin-form-label" for="filtro-semestre">Semestre</label>
                            <select id="filtro-semestre" name="semestre" class="admin-form-input">
                                <option value="">Todos</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" @selected($filtros['semestre'] == $i)>{{ $i }}°</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="admin-form-label" for="filtro-q">Buscar</label>
                            <input id="filtro-q" name="q" type="search" class="admin-form-input" value="{{ $filtros['q'] }}" placeholder="Clave o nombre">
                        </div>
                        <div class="sm:col-span-2">
                            <button type="submit" class="btn-cean-primary">Filtrar</button>
                        </div>
                    </form>
                @endif

                @if (! $licenciaturaSeleccionada)
                    <p class="text-sm text-gray-400">Selecciona una licenciatura en la columna izquierda para ver sus materias.</p>
                @elseif ($materias->isEmpty())
                    <p class="text-sm text-gray-400">No hay materias para los filtros seleccionados. Usa <strong class="text-gray-300">Agregar materia</strong> arriba.</p>
                @else
                    @php
                        $puedeReordenar = blank($filtros['q']);
                    @endphp

                    @if (! $puedeReordenar)
                        <p class="mb-3 text-xs text-gray-500">Quita el filtro de búsqueda para reordenar materias arrastrando las filas.</p>
                    @else
                        <p class="mb-3 text-xs text-gray-500">Arrastra el icono al inicio de cada fila para cambiar el orden dentro del mismo semestre.</p>
                    @endif

                    <div class="overflow-x-auto rounded-xl border border-gray-700">
                        <table class="min-w-full divide-y divide-gray-800 text-sm">
                            <thead class="bg-gray-800/50">
                                <tr>
                                    <th class="w-10 px-2 py-3" aria-label="Orden"></th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-400">Clave</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-400">Materia</th>
                                    <th class="px-4 py-3 text-center font-medium text-gray-400">Sem.</th>
                                    <th class="px-4 py-3 text-center font-medium text-gray-400">Créd.</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-400">Acciones</th>
                                </tr>
                            </thead>
                            <tbody
                                id="materias-sortable"
                                class="divide-y divide-gray-800"
                                data-reorder-url="{{ route('admin.materias.reordenar') }}"
                                data-licenciatura-id="{{ $licenciaturaSeleccionada->id }}"
                                data-can-reorder="{{ $puedeReordenar ? '1' : '0' }}"
                            >
                                @foreach ($materias as $materia)
                                    <tr
                                        class="hover:bg-gray-800/30"
                                        data-id="{{ $materia->id }}"
                                        data-semestre="{{ $materia->semestre }}"
                                    >
                                        <td class="w-10 px-2 py-3">
                                            <button
                                                type="button"
                                                class="materia-drag-handle flex h-8 w-8 items-center justify-center rounded-md text-gray-500 transition {{ $puedeReordenar ? 'cursor-grab hover:bg-gray-800 hover:text-gray-300 active:cursor-grabbing' : 'cursor-not-allowed opacity-30' }}"
                                                aria-label="Arrastrar para reordenar"
                                                @if (! $puedeReordenar) disabled @endif
                                            >
                                                <svg class="h-4 w-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                                    <circle cx="5" cy="4" r="1.25" />
                                                    <circle cx="11" cy="4" r="1.25" />
                                                    <circle cx="5" cy="8" r="1.25" />
                                                    <circle cx="11" cy="8" r="1.25" />
                                                    <circle cx="5" cy="12" r="1.25" />
                                                    <circle cx="11" cy="12" r="1.25" />
                                                </svg>
                                            </button>
                                        </td>
                                        <td class="px-4 py-3 font-mono text-cean-cyan">{{ $materia->clave ?? '—' }}</td>
                                        <td class="px-4 py-3 text-gray-200">{{ $materia->nombre }}</td>
                                        <td class="px-4 py-3 text-center text-gray-400">{{ $materia->etiquetaSemestre() }}</td>
                                        <td class="px-4 py-3 text-center text-gray-400">{{ $materia->creditos ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <button
                                                type="button"
                                                class="text-xs font-medium text-cean-cyan hover:underline"
                                                @click="abrirEditarMateria({{ Js::from([
                                                    'id' => $materia->id,
                                                    'licenciatura_id' => $materia->licenciatura_id,
                                                    'clave' => $materia->clave,
                                                    'nombre' => $materia->nombre,
                                                    'semestre' => $materia->semestre,
                                                    'orden' => $materia->orden,
                                                    'creditos' => $materia->creditos,
                                                ]) }})"
                                            >
                                                Editar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </section>
        </div>

        @if ($licenciaturaSeleccionada)
            {{-- Modal: agregar materia --}}
            <div
                x-show="modalAgregarMateria"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-agregar-materia-titulo"
            >
                <div
                    class="absolute inset-0 bg-black/70 backdrop-blur-sm"
                    @click="modalAgregarMateria = false"
                ></div>

                <div class="admin-modal relative w-full max-w-lg" @click.stop>
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <h3 id="modal-agregar-materia-titulo" class="text-lg font-semibold text-white">Agregar materia</h3>
                            <p class="mt-1 text-sm text-gray-400">{{ $licenciaturaSeleccionada->nombre_corto }}</p>
                        </div>
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-800 hover:text-white"
                            @click="modalAgregarMateria = false"
                            aria-label="Cerrar"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.materias.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="licenciatura_id" value="{{ $licenciaturaSeleccionada->id }}">
                        <div>
                            <label class="admin-form-label" for="nueva-materia-clave">Clave <span class="text-cean-red">*</span></label>
                            <input id="nueva-materia-clave" name="clave" type="text" class="admin-form-input" value="{{ old('licenciatura_id') == $licenciaturaSeleccionada->id ? old('clave') : '' }}" required>
                            @error('clave')
                                <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="admin-form-label" for="nueva-materia-nombre">Nombre <span class="text-cean-red">*</span></label>
                            <input id="nueva-materia-nombre" name="nombre" type="text" class="admin-form-input" value="{{ old('licenciatura_id') == $licenciaturaSeleccionada->id ? old('nombre') : '' }}" required>
                            @error('nombre')
                                <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <label class="admin-form-label" for="nueva-materia-semestre">Semestre <span class="text-cean-red">*</span></label>
                                <input id="nueva-materia-semestre" name="semestre" type="number" min="1" max="12" class="admin-form-input" value="{{ old('licenciatura_id') == $licenciaturaSeleccionada->id ? old('semestre', 1) : 1 }}" required>
                                @error('semestre')
                                    <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="admin-form-label" for="nueva-materia-orden">Orden</label>
                                <input id="nueva-materia-orden" name="orden" type="number" min="1" class="admin-form-input" value="{{ old('licenciatura_id') == $licenciaturaSeleccionada->id ? old('orden') : '' }}" placeholder="Al final">
                                <p class="mt-1 text-[10px] text-gray-500">Opcional. Si queda vacío, va al final del semestre.</p>
                                @error('orden')
                                    <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="admin-form-label" for="nueva-materia-creditos">Créditos <span class="text-cean-red">*</span></label>
                                <input id="nueva-materia-creditos" name="creditos" type="number" step="0.01" min="0" class="admin-form-input" value="{{ old('licenciatura_id') == $licenciaturaSeleccionada->id ? old('creditos') : '' }}" required>
                                @error('creditos')
                                    <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button
                                type="button"
                                class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800"
                                @click="modalAgregarMateria = false"
                            >
                                Cancelar
                            </button>
                            <button type="submit" class="btn-cean-primary">Registrar materia</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Modal: editar materia --}}
        <div
            x-show="modalEditarMateria && materiaEditando"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-editar-materia-titulo"
        >
            <div
                class="absolute inset-0 bg-black/70 backdrop-blur-sm"
                @click="cerrarEditarMateria()"
            ></div>

            <div class="admin-modal relative w-full max-w-lg" @click.stop x-show="materiaEditando">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h3 id="modal-editar-materia-titulo" class="text-lg font-semibold text-white">Editar materia</h3>
                        <p class="mt-1 font-mono text-sm text-cean-cyan" x-text="materiaEditando?.clave"></p>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-800 hover:text-white"
                        @click="cerrarEditarMateria()"
                        aria-label="Cerrar"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form
                    method="POST"
                    x-bind:action="materiaEditando ? '{{ url('/admin/materias') }}/' + materiaEditando.id : '#'"
                    class="space-y-4"
                >
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="materia_edit_id" x-bind:value="materiaEditando?.id">
                    <input type="hidden" name="licenciatura_id" x-bind:value="materiaEditando?.licenciatura_id">
                    <div>
                        <label class="admin-form-label" for="editar-materia-clave">Clave <span class="text-cean-red">*</span></label>
                        <input id="editar-materia-clave" name="clave" type="text" class="admin-form-input" x-model="materiaEditando.clave" required>
                        @error('clave')
                            <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="admin-form-label" for="editar-materia-nombre">Nombre <span class="text-cean-red">*</span></label>
                        <input id="editar-materia-nombre" name="nombre" type="text" class="admin-form-input" x-model="materiaEditando.nombre" required>
                        @error('nombre')
                            <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="admin-form-label" for="editar-materia-semestre">Semestre <span class="text-cean-red">*</span></label>
                            <input id="editar-materia-semestre" name="semestre" type="number" min="1" max="12" class="admin-form-input" x-model="materiaEditando.semestre" required>
                            @error('semestre')
                                <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="admin-form-label" for="editar-materia-orden">Orden</label>
                            <input id="editar-materia-orden" name="orden" type="number" class="admin-form-input" x-model="materiaEditando.orden">
                            @error('orden')
                                <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="admin-form-label" for="editar-materia-creditos">Créditos</label>
                            <input id="editar-materia-creditos" name="creditos" type="number" step="0.01" class="admin-form-input" x-model="materiaEditando.creditos">
                            @error('creditos')
                                <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800"
                            @click="cerrarEditarMateria()"
                        >
                            Cancelar
                        </button>
                        <button type="submit" class="btn-cean-primary">Guardar cambios</button>
                    </div>
                </form>

                <form
                    x-ref="formEliminarMateria"
                    method="POST"
                    x-bind:action="materiaEditando ? '{{ url('/admin/materias') }}/' + materiaEditando.id : '#'"
                    class="hidden"
                >
                    @csrf
                    @method('DELETE')
                </form>

                <div class="mt-4 border-t border-gray-700 pt-4">
                    <button
                        type="button"
                        class="text-sm text-rose-400 hover:underline"
                        @click="modalConfirmarEliminarMateria = true"
                    >
                        Eliminar materia
                    </button>
                </div>
            </div>

            {{-- Modal: confirmar eliminar materia --}}
            <div
                x-show="modalConfirmarEliminarMateria"
                x-cloak
                class="fixed inset-0 z-[60] flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-confirmar-eliminar-materia-titulo"
            >
                <div
                    class="absolute inset-0 bg-black/80 backdrop-blur-sm"
                    @click="modalConfirmarEliminarMateria = false"
                ></div>

                <div class="admin-modal relative w-full max-w-md" @click.stop>
                    <h3 id="modal-confirmar-eliminar-materia-titulo" class="text-lg font-semibold text-white">
                        ¿Eliminar materia?
                    </h3>
                    <p class="mt-2 text-sm text-gray-400">
                        Se eliminará
                        <span class="font-mono text-cean-cyan" x-text="materiaEditando?.clave"></span>
                        —
                        <span x-text="materiaEditando?.nombre"></span>.
                        Esta acción no se puede deshacer.
                    </p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800"
                            @click="modalConfirmarEliminarMateria = false"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-500"
                            @click="$refs.formEliminarMateria.submit()"
                        >
                            Sí, eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if ($licenciaturaSeleccionada)
            {{-- Modal: editar licenciatura --}}
            <div
                x-show="modalEditarLicenciatura"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-editar-licenciatura-titulo"
            >
                <div
                    class="absolute inset-0 bg-black/70 backdrop-blur-sm"
                    @click="modalEditarLicenciatura = false"
                ></div>

                <div class="admin-modal relative w-full max-w-md" @click.stop>
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <h3 id="modal-editar-licenciatura-titulo" class="text-lg font-semibold text-white">Editar licenciatura</h3>
                            <p class="mt-1 text-sm text-gray-400">{{ $licenciaturaSeleccionada->nombre_corto }}</p>
                        </div>
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-800 hover:text-white"
                            @click="modalEditarLicenciatura = false"
                            aria-label="Cerrar"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.licenciaturas.update', $licenciaturaSeleccionada) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="admin-form-label" for="editar-nombre_corto">Nombre corto <span class="text-cean-red">*</span></label>
                            <input id="editar-nombre_corto" name="nombre_corto" type="text" class="admin-form-input" value="{{ old('nombre_corto', $licenciaturaSeleccionada->nombre_corto) }}" required>
                            @error('nombre_corto')
                                <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="admin-form-label" for="editar-clave_dgp">Clave DGP</label>
                            <input
                                id="editar-clave_dgp"
                                name="clave_dgp"
                                type="text"
                                class="admin-form-input font-mono uppercase"
                                value="{{ old('clave_dgp', $licenciaturaSeleccionada->clave_dgp) }}"
                                maxlength="10"
                                pattern="[A-Za-z0-9]{10}"
                                placeholder="10 caracteres (opcional)"
                            >
                            @error('clave_dgp')
                                <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="admin-form-label" for="editar-nombre">Nombre <span class="text-cean-red">*</span></label>
                            <input id="editar-nombre" name="nombre" type="text" class="admin-form-input" value="{{ old('nombre', $licenciaturaSeleccionada->nombre) }}" required>
                            @error('nombre')
                                <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="admin-form-label" for="editar-plan_nombre">Nombre del plan</label>
                            <input id="editar-plan_nombre" name="plan_nombre" type="text" class="admin-form-input" value="{{ old('plan_nombre', $licenciaturaSeleccionada->plan_nombre) }}">
                        </div>
                        <div>
                            <label class="admin-form-label" for="editar-anio_plan">Año del plan</label>
                            <input id="editar-anio_plan" name="anio_plan" type="number" class="admin-form-input" value="{{ old('anio_plan', $licenciaturaSeleccionada->anio_plan) }}">
                            @error('anio_plan')
                                <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                            @enderror
                        </div>
                        <label class="flex items-center gap-2 text-sm text-gray-300">
                            <input type="hidden" name="activa" value="0">
                            <input type="checkbox" name="activa" value="1" @checked(old('activa', $licenciaturaSeleccionada->activa)) class="rounded border-gray-600 bg-gray-800 text-cean-cyan">
                            Licenciatura activa
                        </label>
                        <div class="flex justify-end gap-3 pt-2">
                            <button
                                type="button"
                                class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800"
                                @click="modalEditarLicenciatura = false"
                            >
                                Cancelar
                            </button>
                            <button type="submit" class="btn-cean-primary">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Modal: nueva licenciatura --}}
        <div
            x-show="modalLicenciatura"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-licenciatura-titulo"
        >
            <div
                class="absolute inset-0 bg-black/70 backdrop-blur-sm"
                @click="modalLicenciatura = false"
            ></div>

            <div class="admin-modal relative w-full max-w-md" @click.stop>
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h3 id="modal-licenciatura-titulo" class="text-lg font-semibold text-white">Nueva licenciatura</h3>
                        <p class="mt-1 text-sm text-gray-400">Registra un plan de estudios para el catálogo académico.</p>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-800 hover:text-white"
                        @click="modalLicenciatura = false"
                        aria-label="Cerrar"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.licenciaturas.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="admin-form-label" for="modal-nombre_corto">Nombre corto <span class="text-cean-red">*</span></label>
                        <input id="modal-nombre_corto" name="nombre_corto" type="text" class="admin-form-input" placeholder="TELESECUNDARIA" value="{{ old('nombre_corto') }}" required>
                        @error('nombre_corto')
                            <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="admin-form-label" for="modal-clave_dgp">Clave DGP</label>
                        <input
                            id="modal-clave_dgp"
                            name="clave_dgp"
                            type="text"
                            class="admin-form-input font-mono uppercase"
                            value="{{ old('clave_dgp') }}"
                            maxlength="10"
                            pattern="[A-Za-z0-9]{10}"
                            placeholder="10 caracteres (opcional)"
                        >
                        @error('clave_dgp')
                            <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="admin-form-label" for="modal-nombre">Nombre <span class="text-cean-red">*</span></label>
                        <input id="modal-nombre" name="nombre" type="text" class="admin-form-input" placeholder="Licenciatura en…" value="{{ old('nombre') }}" required>
                        @error('nombre')
                            <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="admin-form-label" for="modal-plan_nombre">Nombre del plan <span class="text-cean-red">*</span></label>
                        <input id="modal-plan_nombre" name="plan_nombre" type="text" class="admin-form-input" placeholder="Plan de estudio de la licenciatura en…" value="{{ old('plan_nombre') }}" required>
                        @error('plan_nombre')
                            <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="admin-form-label" for="modal-anio_plan">Año del plan <span class="text-cean-red">*</span></label>
                        <input id="modal-anio_plan" name="anio_plan" type="number" min="1990" max="2100" class="admin-form-input" value="{{ old('anio_plan') }}" placeholder="2022" required>
                        @error('anio_plan')
                            <p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800"
                            @click="modalLicenciatura = false"
                        >
                            Cancelar
                        </button>
                        <button type="submit" class="btn-cean-primary">Registrar licenciatura</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>

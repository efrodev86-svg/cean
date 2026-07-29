@php
    $periodoEditandoInicial = null;

    if (old('periodo_edit_id')) {
        $periodoEditandoInicial = [
            'id' => (int) old('periodo_edit_id'),
            'clave' => old('clave'),
            'nombre' => old('nombre'),
            'fecha_inicio' => old('fecha_inicio'),
            'fecha_cierre' => old('fecha_cierre'),
            'fecha_entrega_calificaciones' => old('fecha_entrega_calificaciones'),
            'fecha_consulta_boletas' => old('fecha_consulta_boletas'),
            'activo' => (bool) old('activo'),
        ];
    }

    $cicloEditandoInicial = null;

    if (old('ciclo_edit_id')) {
        $cicloEditandoInicial = [
            'id' => (int) old('ciclo_edit_id'),
            'nombre' => old('nombre'),
            'activo' => (bool) old('activo'),
        ];
    }

    $fmt = fn ($fecha) => $fecha ? \Carbon\Carbon::parse($fecha)->format('d/m/Y') : '—';
@endphp

<x-admin-layout title="Ciclos y periodos" breadcrumb="Ciclos y periodos">
    <div
        class="mx-auto max-w-5xl space-y-6"
        x-data="{
            modalNuevoCiclo: @js((bool) old('nuevo_ciclo')),
            modalEditarCiclo: @js((bool) $cicloEditandoInicial),
            modalPeriodo: @js((bool) $periodoEditandoInicial),
            cicloEditando: @js($cicloEditandoInicial),
            periodoEditando: @js($periodoEditandoInicial),
            abrirEditarCiclo(ciclo) { this.cicloEditando = ciclo; this.modalEditarCiclo = true; },
            abrirEditarPeriodo(periodo) { this.periodoEditando = periodo; this.modalPeriodo = true; },
        }"
        @keydown.escape.window="modalNuevoCiclo = false; modalEditarCiclo = false; modalPeriodo = false"
    >
        @if (session('success'))
            <div class="admin-alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="admin-alert-error">{{ session('error') }}</div>
        @endif

        <div class="admin-panel">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-white">Ciclos escolares por sede</h2>
                    <p class="mt-1 text-sm text-gray-400">
                        Cada sede maneja sus propios ciclos. Dentro de cada ciclo hay dos periodos:
                        <strong class="text-gray-300">A</strong> (semestres impares) y <strong class="text-gray-300">B</strong> (pares).
                        Las fechas de cada periodo controlan la captura de calificaciones y la consulta de boletas.
                    </p>
                </div>
                @if ($sedes->isNotEmpty())
                    <button type="button" class="btn-cean-primary text-sm" @click="modalNuevoCiclo = true">
                        Nuevo ciclo
                    </button>
                @endif
            </div>
            @if ($sedes->isEmpty())
                <p class="admin-alert-warning mt-4">
                    Primero registra al menos una <a href="{{ route('admin.sedes.index') }}" class="font-semibold underline">sede</a> para poder crear ciclos.
                </p>
            @endif
        </div>

        @foreach ($sedes as $sede)
            <div class="space-y-3">
                <div class="flex items-center gap-2 px-1">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-cean-cyan">{{ $sede->nombre }}</h2>
                    <span class="font-mono text-[10px] text-gray-500">{{ $sede->clave }}</span>
                </div>

                @forelse ($sede->ciclos as $ciclo)
                    @include('admin.ciclos._ciclo', ['ciclo' => $ciclo, 'fmt' => $fmt])
                @empty
                    <div class="admin-panel text-sm text-gray-400">
                        Esta sede aún no tiene ciclos. Usa <strong class="text-gray-300">Nuevo ciclo</strong> y selecciónala.
                    </div>
                @endforelse
            </div>
        @endforeach

        @if ($ciclosSinSede->isNotEmpty())
            <div class="space-y-3">
                <div class="flex items-center gap-2 px-1">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-amber-400">Sin sede asignada</h2>
                </div>
                @foreach ($ciclosSinSede as $ciclo)
                    @include('admin.ciclos._ciclo', ['ciclo' => $ciclo, 'fmt' => $fmt])
                @endforeach
            </div>
        @endif

        {{-- Modal: nuevo ciclo --}}
        <div x-show="modalNuevoCiclo" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="modalNuevoCiclo = false"></div>
            <div class="admin-modal relative w-full max-w-md" @click.stop>
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Nuevo ciclo escolar</h3>
                        <p class="mt-1 text-sm text-gray-400">Se crearán automáticamente sus periodos A y B.</p>
                    </div>
                    <button type="button" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-800 hover:text-white" @click="modalNuevoCiclo = false" aria-label="Cerrar">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.ciclos.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="nuevo_ciclo" value="1">
                    <div>
                        <label class="admin-form-label" for="nuevo-ciclo-sede">Sede <span class="text-cean-red">*</span></label>
                        <select id="nuevo-ciclo-sede" name="sede_id" class="admin-form-input" required>
                            <option value="">Selecciona una sede…</option>
                            @foreach ($sedes as $sede)
                                <option value="{{ $sede->id }}" @selected(old('sede_id') == $sede->id)>{{ $sede->nombre }} ({{ $sede->clave }})</option>
                            @endforeach
                        </select>
                        @if (old('nuevo_ciclo'))@error('sede_id')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                    </div>
                    <div>
                        <label class="admin-form-label" for="nuevo-ciclo-nombre">Nombre del ciclo <span class="text-cean-red">*</span></label>
                        <input id="nuevo-ciclo-nombre" name="nombre" type="text" class="admin-form-input" value="{{ old('nuevo_ciclo') ? old('nombre') : '' }}" placeholder="2024-2025" required>
                        @if (old('nuevo_ciclo'))@error('nombre')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800" @click="modalNuevoCiclo = false">Cancelar</button>
                        <button type="submit" class="btn-cean-primary">Crear ciclo</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: editar ciclo --}}
        <div x-show="modalEditarCiclo && cicloEditando" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="modalEditarCiclo = false"></div>
            <div class="admin-modal relative w-full max-w-md" @click.stop>
                <div class="mb-5 flex items-start justify-between gap-4">
                    <h3 class="text-lg font-semibold text-white">Editar ciclo escolar</h3>
                    <button type="button" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-800 hover:text-white" @click="modalEditarCiclo = false" aria-label="Cerrar">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form method="POST" x-bind:action="cicloEditando ? '{{ url('/admin/ciclos') }}/' + cicloEditando.id : '#'" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="ciclo_edit_id" x-bind:value="cicloEditando?.id">
                    <div>
                        <label class="admin-form-label" for="editar-ciclo-nombre">Nombre del ciclo <span class="text-cean-red">*</span></label>
                        <input id="editar-ciclo-nombre" name="nombre" type="text" class="admin-form-input" x-model="cicloEditando.nombre" required>
                        @if (old('ciclo_edit_id'))@error('nombre')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-300">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" name="activo" value="1" x-model="cicloEditando.activo" class="rounded border-gray-600 bg-gray-800 text-cean-cyan">
                        Ciclo activo (solo uno por sede)
                    </label>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800" @click="modalEditarCiclo = false">Cancelar</button>
                        <button type="submit" class="btn-cean-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: editar periodo --}}
        <div x-show="modalPeriodo && periodoEditando" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="modalPeriodo = false"></div>
            <div class="admin-modal relative w-full max-w-lg" @click.stop>
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Editar periodo <span x-text="periodoEditando?.clave"></span></h3>
                        <p class="mt-1 text-sm text-gray-400">Las fechas definen las reglas de captura y consulta.</p>
                    </div>
                    <button type="button" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-800 hover:text-white" @click="modalPeriodo = false" aria-label="Cerrar">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form method="POST" x-bind:action="periodoEditando ? '{{ url('/admin/periodos') }}/' + periodoEditando.id : '#'" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="periodo_edit_id" x-bind:value="periodoEditando?.id">
                    <div>
                        <label class="admin-form-label" for="periodo-nombre">Nombre del periodo <span class="text-cean-red">*</span></label>
                        <input id="periodo-nombre" name="nombre" type="text" class="admin-form-input" x-model="periodoEditando.nombre" required>
                        @if (old('periodo_edit_id'))@error('nombre')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="admin-form-label" for="periodo-inicio">Inicio del periodo</label>
                            <input id="periodo-inicio" name="fecha_inicio" type="date" class="admin-form-input" x-model="periodoEditando.fecha_inicio">
                            @if (old('periodo_edit_id'))@error('fecha_inicio')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                        </div>
                        <div>
                            <label class="admin-form-label" for="periodo-cierre">Cierre del periodo</label>
                            <input id="periodo-cierre" name="fecha_cierre" type="date" class="admin-form-input" x-model="periodoEditando.fecha_cierre">
                            @if (old('periodo_edit_id'))@error('fecha_cierre')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                        </div>
                        <div>
                            <label class="admin-form-label" for="periodo-entrega">Entrega de calificaciones</label>
                            <input id="periodo-entrega" name="fecha_entrega_calificaciones" type="date" class="admin-form-input" x-model="periodoEditando.fecha_entrega_calificaciones">
                            <p class="mt-1 text-[10px] text-gray-500">Límite para que docentes/control suban calificaciones.</p>
                            @if (old('periodo_edit_id'))@error('fecha_entrega_calificaciones')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                        </div>
                        <div>
                            <label class="admin-form-label" for="periodo-consulta">Consulta de boletas</label>
                            <input id="periodo-consulta" name="fecha_consulta_boletas" type="date" class="admin-form-input" x-model="periodoEditando.fecha_consulta_boletas">
                            <p class="mt-1 text-[10px] text-gray-500">Desde esta fecha el alumno puede ver su boleta.</p>
                            @if (old('periodo_edit_id'))@error('fecha_consulta_boletas')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-300">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" name="activo" value="1" x-model="periodoEditando.activo" class="rounded border-gray-600 bg-gray-800 text-cean-cyan">
                        Periodo en curso
                    </label>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800" @click="modalPeriodo = false">Cancelar</button>
                        <button type="submit" class="btn-cean-primary">Guardar fechas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>

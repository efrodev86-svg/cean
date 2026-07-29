<section class="admin-panel">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="flex items-center gap-3">
            <h3 class="text-xl font-bold text-white">{{ $ciclo->nombre }}</h3>
            <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $ciclo->activo ? 'bg-green-900/40 text-green-300' : 'bg-gray-700 text-gray-400' }}">
                {{ $ciclo->activo ? 'Activo' : 'Inactivo' }}
            </span>
            <span class="text-xs text-gray-500">{{ $ciclo->alumnos_count }} alumno(s)</span>
        </div>
        <button
            type="button"
            class="rounded-lg border border-gray-600 px-3 py-1.5 text-xs font-medium text-gray-200 transition hover:border-cean-cyan/50 hover:text-cean-cyan"
            @click="abrirEditarCiclo({{ Js::from(['id' => $ciclo->id, 'nombre' => $ciclo->nombre, 'activo' => $ciclo->activo]) }})"
        >
            Editar ciclo
        </button>
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        @foreach ($ciclo->periodos as $periodo)
            <div class="rounded-xl border border-gray-700 bg-gray-900/40 p-4">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="font-semibold text-cean-cyan">Periodo {{ $periodo->clave }}</p>
                        <p class="mt-0.5 text-xs text-gray-400">{{ $periodo->nombre }}</p>
                        <p class="mt-1 text-[10px] uppercase tracking-wide text-gray-500">Semestres {{ $periodo->paridad() }}</p>
                    </div>
                    @if ($periodo->activo)
                        <span class="shrink-0 rounded-full bg-cean-cyan/20 px-2 py-0.5 text-[10px] font-semibold uppercase text-cean-cyan">En curso</span>
                    @endif
                </div>

                <dl class="mt-3 space-y-1.5 text-xs">
                    <div class="flex justify-between gap-2">
                        <dt class="text-gray-500">Inicio</dt>
                        <dd class="text-gray-200">{{ $fmt($periodo->fecha_inicio) }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-gray-500">Cierre</dt>
                        <dd class="text-gray-200">{{ $fmt($periodo->fecha_cierre) }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-gray-500">Entrega de calificaciones</dt>
                        <dd class="text-gray-200">{{ $fmt($periodo->fecha_entrega_calificaciones) }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-gray-500">Consulta de boletas</dt>
                        <dd class="text-gray-200">{{ $fmt($periodo->fecha_consulta_boletas) }}</dd>
                    </div>
                </dl>

                <button
                    type="button"
                    class="mt-3 text-xs font-medium text-cean-cyan hover:underline"
                    @click="abrirEditarPeriodo({{ Js::from([
                        'id' => $periodo->id,
                        'clave' => $periodo->clave,
                        'nombre' => $periodo->nombre,
                        'fecha_inicio' => optional($periodo->fecha_inicio)->format('Y-m-d'),
                        'fecha_cierre' => optional($periodo->fecha_cierre)->format('Y-m-d'),
                        'fecha_entrega_calificaciones' => optional($periodo->fecha_entrega_calificaciones)->format('Y-m-d'),
                        'fecha_consulta_boletas' => optional($periodo->fecha_consulta_boletas)->format('Y-m-d'),
                        'activo' => $periodo->activo,
                    ]) }})"
                >
                    Editar fechas
                </button>
            </div>
        @endforeach
    </div>
</section>

<x-admin-layout title="Grupos" breadcrumb="Grupos">
    <div class="mx-auto max-w-5xl space-y-6">
        @if (session('success'))
            <div class="admin-alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="admin-alert-error">{{ session('error') }}</div>
        @endif

        <div class="admin-panel">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-white">Grupos escolares</h2>
                    <p class="mt-1 text-sm text-gray-400">
                        @if ($cicloSeleccionado)
                            Ciclo <strong class="text-gray-300">{{ $cicloSeleccionado->nombre }}</strong>
                            @if ($sedeSeleccionada)
                                · {{ $sedeSeleccionada->nombre }}
                            @endif
                            · {{ $grupos->count() }} grupo(s) · {{ $totalAlumnos }} alumno(s)
                        @else
                            Selecciona una sede con ciclo escolar para ver los grupos.
                        @endif
                    </p>
                </div>
                @if ($cicloSeleccionado)
                    <a
                        href="{{ route('admin.grupos.create', array_filter(['ciclo' => $filtros['ciclo'], 'sede' => $filtros['sede']])) }}"
                        class="btn-cean-primary text-sm"
                    >
                        Nuevo grupo
                    </a>
                @endif
            </div>

            <form method="GET" action="{{ route('admin.grupos') }}" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @if ($esAdminGlobal)
                    <div>
                        <label class="admin-form-label" for="filtro-sede">Sede</label>
                        <select id="filtro-sede" name="sede" class="admin-form-input" onchange="this.form.submit()">
                            @foreach ($sedes as $sede)
                                <option value="{{ $sede->id }}" @selected($filtros['sede'] == $sede->id)>{{ $sede->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="admin-form-label" for="filtro-ciclo">Ciclo escolar</label>
                    <select id="filtro-ciclo" name="ciclo" class="admin-form-input" onchange="this.form.submit()">
                        @forelse ($ciclos as $ciclo)
                            <option value="{{ $ciclo->id }}" @selected($filtros['ciclo'] == $ciclo->id)>
                                {{ $ciclo->nombre }}{{ $ciclo->activo ? ' (activo)' : '' }}
                            </option>
                        @empty
                            <option value="">Sin ciclos</option>
                        @endforelse
                    </select>
                </div>

                <div>
                    <label class="admin-form-label" for="filtro-licenciatura">Licenciatura</label>
                    <select id="filtro-licenciatura" name="licenciatura" class="admin-form-input" onchange="this.form.submit()">
                        <option value="">Todas</option>
                        @foreach ($licenciaturas as $licenciatura)
                            <option value="{{ $licenciatura }}" @selected($filtros['licenciatura'] === $licenciatura)>
                                {{ $licenciatura }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <div class="admin-panel">
            @if (! $cicloSeleccionado)
                <p class="text-sm text-gray-400">No hay un ciclo escolar disponible para esta sede.</p>
            @elseif ($grupos->isEmpty())
                <p class="text-sm text-gray-400">No hay grupos registrados en este ciclo con los filtros seleccionados.</p>
            @else
                <div class="overflow-x-auto rounded-xl border border-gray-700">
                    <table class="min-w-full divide-y divide-gray-800 text-sm">
                        <thead class="bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Grupo</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Semestre</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Licenciatura</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Alumnos</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-400">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach ($grupos as $grupo)
                                <tr class="hover:bg-gray-800/30">
                                    <td class="px-4 py-3">
                                        <span class="font-semibold text-white">{{ $grupo->clave() }}</span>
                                        <p class="text-xs text-gray-500">{{ $grupo->nombre }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-400">{{ $grupo->semestre }}°</td>
                                    <td class="px-4 py-3 text-gray-400">{{ $grupo->licenciatura }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full bg-cean-cyan/15 px-2.5 py-0.5 text-xs font-semibold text-cean-cyan">
                                            {{ $grupo->alumnos_count }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <a
                                                href="{{ route('admin.grupos.asignaciones.edit', $grupo) }}"
                                                class="text-xs font-medium text-cean-cyan hover:underline"
                                            >
                                                Asignaciones
                                            </a>
                                            <a
                                                href="{{ route('admin.alumnos', ['ciclo' => $filtros['ciclo'], 'grupo' => $grupo->id] + ($esAdminGlobal ? ['sede' => $filtros['sede']] : [])) }}"
                                                class="text-xs font-medium text-gray-300 hover:text-white hover:underline"
                                            >
                                                Ver alumnos
                                            </a>
                                            <a
                                                href="{{ route('admin.grupos.edit', $grupo) }}"
                                                class="text-xs font-medium text-gray-300 hover:text-white hover:underline"
                                            >
                                                Editar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>

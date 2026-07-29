<x-admin-layout title="Alumnos" breadcrumb="Alumnos">
    <div class="mx-auto max-w-6xl space-y-6">
        @if (session('success'))
            <div class="admin-alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="admin-alert-error">{{ session('error') }}</div>
        @endif

        <div class="admin-panel">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-white">Catálogo de alumnos</h2>
                    <p class="mt-1 text-sm text-gray-400">
                        @if ($cicloSeleccionado)
                            Ciclo <strong class="text-gray-300">{{ $cicloSeleccionado->nombre }}</strong>
                            @if ($sedeSeleccionada)
                                · {{ $sedeSeleccionada->nombre }}
                            @endif
                            · {{ $alumnos->total() }} alumno(s)
                        @else
                            Selecciona una sede con ciclo escolar para ver el catálogo.
                        @endif
                    </p>
                </div>
                <a
                    href="{{ route('admin.alumnos.create', array_filter(['ciclo' => $filtros['ciclo'], 'grupo' => $filtros['grupo'], 'sede' => $filtros['sede']])) }}"
                    class="btn-cean-primary text-sm"
                >
                    Nuevo alumno
                </a>
            </div>

            <form method="GET" action="{{ route('admin.alumnos') }}" class="mt-4 space-y-3">
                <div class="grid gap-3 {{ $esAdminGlobal ? 'sm:grid-cols-3' : 'sm:grid-cols-2' }}">
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
                        <label class="admin-form-label" for="filtro-grupo">Grupo</label>
                        <select id="filtro-grupo" name="grupo" class="admin-form-input" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            @foreach ($grupos as $grupo)
                                <option value="{{ $grupo->id }}" @selected($filtros['grupo'] == $grupo->id)>
                                    {{ $grupo->etiqueta() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="w-full">
                    <label class="admin-form-label" for="filtro-q">Buscar</label>
                    <div class="flex w-full gap-2">
                        <input
                            id="filtro-q"
                            name="q"
                            type="search"
                            value="{{ $filtros['q'] }}"
                            placeholder="Matrícula o nombre"
                            class="admin-form-input min-w-0 flex-1 basis-0"
                        >
                        <button type="submit" class="btn-cean-primary w-auto shrink-0 px-6 text-sm">Buscar</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="admin-panel">
            @if (! $cicloSeleccionado)
                <p class="text-sm text-gray-400">No hay un ciclo escolar disponible para esta sede.</p>
            @elseif ($alumnos->isEmpty())
                <p class="text-sm text-gray-400">No hay alumnos registrados en este ciclo con los filtros seleccionados.</p>
            @else
                <div class="overflow-x-auto rounded-xl border border-gray-700">
                    <table class="min-w-full divide-y divide-gray-800 text-sm">
                        <thead class="bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Matrícula</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Nombre</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Grupo</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Licenciatura</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Correo de acceso</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Estatus</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Acceso</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-400">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach ($alumnos as $alumno)
                                <tr class="hover:bg-gray-800/30">
                                    <td class="px-4 py-3 font-mono text-xs text-cean-cyan">{{ $alumno->matricula ?: '—' }}</td>
                                    <td class="px-4 py-3 text-gray-200">{{ $alumno->nombreFormal() }}</td>
                                    <td class="px-4 py-3 text-gray-400">{{ $alumno->semestreGrupo() }}</td>
                                    <td class="px-4 py-3 text-gray-400">{{ $alumno->licenciatura }}</td>
                                    <td class="px-4 py-3 text-gray-400">{{ $alumno->emailAcceso() ?? $alumno->user?->email ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ \App\Support\AlumnoEstatus::bloqueaAccesoPortal($alumno->estatus) ? 'bg-cean-red/15 text-red-300' : 'bg-gray-700 text-gray-300' }}">
                                            {{ $alumno->etiquetaEstatus() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($alumno->user)
                                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $alumno->user->activo ? 'bg-green-900/40 text-green-300' : 'bg-gray-700 text-gray-400' }}">
                                                {{ $alumno->user->activo ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-600">Sin cuenta</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.alumnos.edit', $alumno) }}" class="text-xs font-medium text-cean-cyan hover:underline">
                                            Editar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $alumnos->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>

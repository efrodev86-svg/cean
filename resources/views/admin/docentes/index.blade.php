<x-admin-layout title="Docentes" breadcrumb="Docentes">
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
                    <h2 class="text-lg font-semibold text-white">Docentes</h2>
                    <p class="mt-1 text-sm text-gray-400">
                        Cada docente es un único registro. Si imparte en varias sedes, se le asignan todas
                        @if ($esAdminGlobal)
                            al registrarlo.
                        @else
                            desde control escolar de cada sede; aquí gestionas los de tu sede.
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.docentes.grados-academicos.index') }}" class="rounded-lg border border-gray-600 px-3 py-1.5 text-sm font-medium text-gray-300 transition hover:border-cean-cyan/50 hover:text-cean-cyan">
                        Grados académicos
                    </a>
                    <a href="{{ route('admin.docentes.create') }}" class="btn-cean-primary text-sm">
                        Nuevo docente
                    </a>
                </div>
            </div>

            @if ($esAdminGlobal)
                <form method="GET" action="{{ route('admin.docentes.index') }}" class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="admin-form-label" for="filtro-sede">Sede</label>
                        <select id="filtro-sede" name="sede" class="admin-form-input" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            @foreach ($sedes as $sede)
                                <option value="{{ $sede->id }}" @selected($filtros['sede'] == $sede->id)>{{ $sede->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            @endif
        </div>

        <div class="admin-panel">
            @if ($docentes->isEmpty())
                <p class="text-sm text-gray-400">No hay docentes registrados todavía.</p>
            @else
                <div class="overflow-x-auto rounded-xl border border-gray-700">
                    <table class="min-w-full divide-y divide-gray-800 text-sm">
                        <thead class="bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Nombre</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Correo</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Contratación</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Celular</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Sedes</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Estado</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-400">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach ($docentes as $docente)
                                <tr class="hover:bg-gray-800/30">
                                    <td class="px-4 py-3 text-gray-200">{{ $docente->nombreConGrado() }}</td>
                                    <td class="px-4 py-3 text-gray-400">{{ $docente->email }}</td>
                                    <td class="px-4 py-3 text-gray-400">{{ $docente->tipoContratacionLabel() ?: '—' }}</td>
                                    <td class="px-4 py-3 text-gray-400">{{ $docente->celular ?: '—' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse ($docente->sedes as $sede)
                                                <span class="rounded-full bg-cean-cyan/15 px-2 py-0.5 text-[10px] font-semibold text-cean-cyan">{{ $sede->nombre }}</span>
                                            @empty
                                                <span class="text-xs text-gray-600">Sin sede</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $docente->activo ? 'bg-green-900/40 text-green-300' : 'bg-gray-700 text-gray-400' }}">
                                            {{ $docente->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.docentes.edit', $docente) }}" class="text-xs font-medium text-cean-cyan hover:underline">
                                            Editar
                                        </a>
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

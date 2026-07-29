<x-admin-layout title="Calificaciones" breadcrumb="Calificaciones">
    <div class="mx-auto max-w-4xl space-y-6">
        @if (session('success'))
            <div class="admin-alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="admin-alert-error">{{ session('error') }}</div>
        @endif
        @if (session('import_errors'))
            <div class="admin-alert-warning">
                <p class="font-medium">Advertencias durante la importación:</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach (session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                @if (session('import_errors_more'))
                    <p class="mt-2">… y {{ session('import_errors_more') }} error(es) más.</p>
                @endif
            </div>
        @endif

        @if ($sedes->isNotEmpty())
            <form method="GET" action="{{ route('admin.calificaciones.index') }}" class="admin-panel flex flex-wrap items-end gap-3">
                <div class="min-w-[220px] flex-1">
                    <label for="sede" class="admin-form-label">Sede</label>
                    <select id="sede" name="sede" class="admin-form-input" onchange="this.form.submit()">
                        @foreach ($sedes as $sede)
                            <option value="{{ $sede->id }}" @selected($sedeSeleccionada?->id === $sede->id)>{{ $sede->nombre }} ({{ $sede->clave }})</option>
                        @endforeach
                    </select>
                </div>
                <p class="text-xs text-gray-500">
                    @if ($ciclo)
                        Ciclo activo de la sede: <strong class="text-gray-300">{{ $ciclo->nombre }}</strong>
                    @else
                        Esta sede no tiene un ciclo activo.
                    @endif
                </p>
            </form>
        @endif

        <div class="admin-panel">
            <h3 class="text-lg font-semibold text-white">Importar desde CSV</h3>
            <p class="mt-2 text-sm text-gray-400">
                Formato: <code class="rounded bg-gray-800 px-1 font-mono text-cean-cyan">matricula,materia,calificacion,asistencia</code>
                (asistencia % es opcional, default 100). La primera fila puede ser encabezado.
            </p>

            @if (! $ciclo)
                <p class="admin-alert-error mt-4">Activa un ciclo escolar para poder importar.</p>
            @else
                <form method="POST" action="{{ route('admin.calificaciones.importar') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                    @csrf
                    <input type="hidden" name="sede_id" value="{{ $sedeSeleccionada?->id }}">

                    <div>
                        <label for="semestre" class="admin-form-label">Semestre</label>
                        <select id="semestre" name="semestre" class="admin-form-input" required>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" @selected(old('semestre') == $i)>{{ $i }}° Semestre ({{ $i % 2 === 0 ? 'Par' : 'Impar' }})</option>
                            @endfor
                        </select>
                        <x-input-error :messages="$errors->get('semestre')" class="mt-2" />
                    </div>

                    <div>
                        <label for="archivo" class="admin-form-label">Archivo CSV</label>
                        <input id="archivo" name="archivo" type="file" accept=".csv,.txt"
                            class="admin-form-input file:mr-4 file:rounded-md file:border-0 file:bg-cean-cyan/20 file:px-3 file:py-1 file:text-sm file:text-cean-cyan" required />
                        <x-input-error :messages="$errors->get('archivo')" class="mt-2" />
                    </div>

                    <button type="submit" class="btn-cean-primary">Importar calificaciones</button>
                </form>

                <div class="mt-6 rounded-lg border border-gray-700 bg-gray-800/50 p-4 text-sm text-gray-400">
                    <p class="font-medium text-gray-300">Ejemplo de archivo:</p>
                    <pre class="mt-2 overflow-x-auto font-mono text-xs text-gray-500">matricula,materia,calificacion,asistencia
201559590000,APRENDIZAJE EN EL SERVICIO,9,95
201559590000,DIDÁCTICA DE LAS MATEMÁTICAS,8.5,90</pre>
                </div>
            @endif
        </div>

        @if ($ciclo && $alumnos->isNotEmpty())
            <div class="admin-panel overflow-hidden p-0">
                <div class="border-b border-gray-800 px-6 py-4">
                    <h3 class="font-medium text-white">Alumnos del ciclo {{ $ciclo->nombre }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-800 text-sm">
                        <thead class="bg-gray-800/50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-400">Matrícula</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-400">Nombre</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-400">Semestre</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-400">Grupo</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-400">Licenciatura</th>
                                <th class="px-6 py-3 text-center font-medium text-gray-400">Calificaciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach ($alumnos as $alumno)
                                <tr class="hover:bg-gray-800/30">
                                    <td class="px-6 py-3 font-mono text-gray-300">{{ $alumno->matricula }}</td>
                                    <td class="px-6 py-3 text-gray-200">{{ $alumno->nombreCompleto() }}</td>
                                    <td class="px-6 py-3 text-gray-400">{{ $alumno->semestre }}°</td>
                                    <td class="px-6 py-3 text-gray-400">{{ $alumno->grupo }}</td>
                                    <td class="px-6 py-3 text-gray-400">{{ $alumno->licenciatura }}</td>
                                    <td class="px-6 py-3 text-center text-cean-cyan">{{ $alumno->calificaciones_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-admin-layout>

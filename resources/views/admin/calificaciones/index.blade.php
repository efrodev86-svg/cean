<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Carga de calificaciones
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div>
            @endif
            @if (session('import_errors'))
                <div class="rounded-md bg-amber-50 p-4 text-sm text-amber-800">
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

            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="text-lg font-medium text-gray-900">Importar desde CSV</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Formato: <code class="rounded bg-gray-100 px-1">matricula,materia,calificacion,faltas</code>
                    (faltas es opcional). La primera fila puede ser encabezado.
                </p>

                @if (! $ciclo)
                    <p class="mt-4 text-sm text-red-600">Activa un ciclo escolar para poder importar.</p>
                @else
                    <form method="POST" action="{{ route('admin.calificaciones.importar') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                        @csrf

                        <div>
                            <x-input-label for="bimestre" value="Bimestre" />
                            <select id="bimestre" name="bimestre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" @selected(old('bimestre') == $i)>{{ $i }}° Bimestre</option>
                                @endfor
                            </select>
                            <x-input-error :messages="$errors->get('bimestre')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="archivo" value="Archivo CSV" />
                            <input id="archivo" name="archivo" type="file" accept=".csv,.txt"
                                class="mt-1 block w-full text-sm text-gray-600" required />
                            <x-input-error :messages="$errors->get('archivo')" class="mt-2" />
                        </div>

                        <x-primary-button>Importar calificaciones</x-primary-button>
                    </form>

                    <div class="mt-6 rounded-md bg-gray-50 p-4 text-sm text-gray-700">
                        <p class="font-medium">Ejemplo de archivo:</p>
                        <pre class="mt-2 overflow-x-auto text-xs">matricula,materia,calificacion,faltas
2025001,Matemáticas,9.0,2
2025001,Español,8.5,0
2025002,Matemáticas,7.8,1</pre>
                    </div>
                @endif
            </div>

            @if ($ciclo && $alumnos->isNotEmpty())
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="border-b px-6 py-4">
                        <h3 class="font-medium text-gray-900">Alumnos del ciclo {{ $ciclo->nombre }}</h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Matrícula</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Nombre</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Grado / Grupo</th>
                                <th class="px-6 py-3 text-center font-medium text-gray-500">Calificaciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($alumnos as $alumno)
                                <tr>
                                    <td class="px-6 py-3">{{ $alumno->matricula }}</td>
                                    <td class="px-6 py-3">{{ $alumno->nombreCompleto() }}</td>
                                    <td class="px-6 py-3">{{ $alumno->grado }} — {{ $alumno->grupo }}</td>
                                    <td class="px-6 py-3 text-center">{{ $alumno->calificaciones_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

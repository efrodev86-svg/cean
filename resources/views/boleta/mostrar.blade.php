<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Boleta — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans text-gray-900 antialiased">
    <div class="mx-auto max-w-4xl px-4 py-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Boleta de calificaciones</h1>
                <p class="text-sm text-gray-600">{{ config('app.name') }} — {{ $ciclo->nombre }}</p>
            </div>
            <button onclick="window.print()" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                Imprimir
            </button>
        </div>

        <div class="mb-6 rounded-lg bg-white p-6 shadow">
            <dl class="grid gap-2 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase text-gray-500">Alumno</dt>
                    <dd class="font-semibold">{{ $alumno->nombreCompleto() }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-gray-500">Matrícula</dt>
                    <dd>{{ $alumno->matricula }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-gray-500">Grado y grupo</dt>
                    <dd>{{ $alumno->grado }} — Grupo {{ $alumno->grupo }}</dd>
                </div>
            </dl>
        </div>

        @forelse ($calificacionesPorBimestre as $bimestre => $calificaciones)
            <div class="mb-6 overflow-hidden rounded-lg bg-white shadow">
                <div class="border-b bg-indigo-50 px-6 py-3">
                    <h2 class="font-semibold text-indigo-900">{{ $bimestre }}° Bimestre</h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Materia</th>
                            <th class="px-6 py-3 text-center font-medium text-gray-500">Calificación</th>
                            <th class="px-6 py-3 text-center font-medium text-gray-500">Faltas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($calificaciones as $calificacion)
                            <tr>
                                <td class="px-6 py-3">{{ $calificacion->materia->nombre }}</td>
                                <td class="px-6 py-3 text-center font-semibold">{{ number_format($calificacion->calificacion, 1) }}</td>
                                <td class="px-6 py-3 text-center">{{ $calificacion->faltas }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <div class="rounded-lg bg-white p-6 text-center text-gray-600 shadow">
                Aún no hay calificaciones publicadas para este ciclo.
            </div>
        @endforelse

        <p class="text-center text-sm">
            <a href="{{ route('boleta.index') }}" class="text-indigo-600 hover:text-indigo-500">Nueva consulta</a>
        </p>
    </div>
</body>
</html>

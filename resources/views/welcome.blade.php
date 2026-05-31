<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-b from-indigo-50 to-white font-sans text-gray-900 antialiased">
    <div class="mx-auto flex min-h-screen max-w-4xl flex-col items-center justify-center px-4 py-16">
        <h1 class="text-center text-4xl font-bold tracking-tight text-indigo-900 sm:text-5xl">
            {{ config('app.name') }}
        </h1>
        <p class="mt-4 max-w-xl text-center text-lg text-gray-600">
            Sistema de control escolar. Consulta boletas y gestión de calificaciones.
        </p>

        <div class="mt-10 grid w-full max-w-lg gap-4 sm:grid-cols-2">
            <a href="{{ route('boleta.index') }}"
                class="rounded-xl bg-indigo-600 px-6 py-4 text-center font-semibold text-white shadow-lg transition hover:bg-indigo-500">
                Soy alumno
                <span class="mt-1 block text-sm font-normal text-indigo-100">Consultar mi boleta</span>
            </a>

            @auth
                @if (Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}"
                        class="rounded-xl border-2 border-indigo-600 px-6 py-4 text-center font-semibold text-indigo-700 transition hover:bg-indigo-50">
                        Panel control escolar
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}"
                    class="rounded-xl border-2 border-indigo-600 px-6 py-4 text-center font-semibold text-indigo-700 transition hover:bg-indigo-50">
                    Personal escolar
                    <span class="mt-1 block text-sm font-normal text-gray-500">Iniciar sesión</span>
                </a>
            @endauth
        </div>

        @auth
            <form method="POST" action="{{ route('logout') }}" class="mt-8">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Cerrar sesión</button>
            </form>
        @endauth
    </div>
</body>
</html>

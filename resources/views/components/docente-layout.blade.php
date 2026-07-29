@props([
    'title' => 'Portal docente',
    'breadcrumb' => 'Portal',
])

@php
    $user = auth()->user();
    $iniciales = collect(explode(' ', $user->name))
        ->filter()
        ->take(2)
        ->map(fn (string $parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
        ->implode('');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} — {{ config('cean.acronym') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-screen bg-gray-950 text-gray-100">
        {{-- Overlay móvil --}}
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-linear duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-black/60 lg:hidden"
            @click="sidebarOpen = false"
            x-cloak
        ></div>

        {{-- Sidebar --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="docente-sidebar fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-gray-800 bg-gray-900 transition-transform duration-200 lg:static lg:translate-x-0"
        >
            <div class="flex items-center gap-3 border-b border-gray-800 px-5 py-5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white p-1">
                    <img src="{{ asset('images/cean-mark.svg') }}" alt="{{ config('cean.acronym') }}" class="h-full w-full object-contain">
                </div>
                <div>
                    <p class="text-sm font-bold tracking-wide text-white">{{ config('cean.acronym') }}</p>
                    <span class="mt-1 inline-block rounded-full bg-cean-cyan/20 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-cean-cyan">
                        {{ $user->isEncargadoDocente() ? 'Encargado-docente' : 'Docente' }}
                    </span>
                </div>
            </div>

            <nav class="flex-1 space-y-1 px-3 py-4" aria-label="Navegación docente">
                <x-docente.nav-link :href="route('docente.dashboard')" :active="request()->routeIs('docente.dashboard')" icon="home">
                    Inicio
                </x-docente.nav-link>
                <x-docente.nav-link :href="route('docente.materias')" :active="request()->routeIs('docente.materias')" icon="materias">
                    Mis materias
                </x-docente.nav-link>
                <x-docente.nav-link :href="route('docente.calificaciones')" :active="request()->routeIs('docente.calificaciones')" icon="calificaciones">
                    Captura calificaciones
                </x-docente.nav-link>
                <x-docente.nav-link :href="route('docente.alumnos')" :active="request()->routeIs('docente.alumnos')" icon="alumnos">
                    Mis alumnos
                </x-docente.nav-link>
                <x-docente.nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')" icon="perfil">
                    Mi perfil
                </x-docente.nav-link>
                @if ($user->isControlEscolar())
                    <div class="border-t border-gray-800 pt-3 mt-3">
                        <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-wide text-gray-500">Control escolar</p>
                        <x-docente.nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')" icon="home">
                            Panel de control escolar
                        </x-docente.nav-link>
                    </div>
                @endif
            </nav>

            <div class="border-t border-gray-800 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-cean-navy text-sm font-semibold text-white">
                        {{ $iniciales }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-white">{{ $user->name }}</p>
                        @if ($user->codigo)
                            <p class="font-mono text-[10px] text-gray-500">ID: {{ $user->codigo }}</p>
                        @endif
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full rounded-lg px-2 py-1.5 text-left text-xs text-gray-500 transition hover:bg-gray-800 hover:text-gray-300">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        {{-- Contenido principal --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="docente-topbar flex items-center justify-between border-b border-gray-800 bg-gray-900/80 px-4 py-4 backdrop-blur sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="rounded-lg p-2 text-gray-400 hover:bg-gray-800 hover:text-white lg:hidden"
                        @click="sidebarOpen = true"
                        aria-label="Abrir menú"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <div>
                        <p class="text-xs text-gray-500">
                            <span>Inicio</span>
                            <span class="mx-1">›</span>
                            <span class="text-gray-400">{{ $breadcrumb }}</span>
                        </p>
                        <h1 class="text-xl font-bold text-white sm:text-2xl">{{ $title }}</h1>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-800 hover:text-white" aria-label="Notificaciones">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.031A7.967 7.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a7.967 7.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.031m-5.714 0a24.255 24.255 0 0 1-1.155-.022m1.155.022a24.253 24.253 0 0 0 0-3.977m0 0a23.94 23.94 0 0 1-5.857-1.022M15 17.25h-3.75m-7.5 0h7.5m-7.5 0-1.5h9a1.5 1.5 0 0 0 1.5-1.5v-9a1.5 1.5 0 0 0-1.5-1.5h-9a1.5 1.5 0 0 0-1.5 1.5v9a1.5 1.5 0 0 0 1.5 1.5Z" />
                        </svg>
                    </button>
                    <a href="{{ route('profile.edit') }}" class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-800 hover:text-white" aria-label="Configuración">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </a>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>

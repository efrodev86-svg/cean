@props([
    'title' => 'Panel de control ENSQ',
    'breadcrumb' => 'Inicio',
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
<body
    class="font-sans antialiased"
    x-data="{
        sidebarOpen: false,
        sidebarCollapsed: localStorage.getItem('admin-sidebar-collapsed') === '1',
        userMenuOpen: false,
        toggleSidebarCollapsed() {
            this.sidebarCollapsed = ! this.sidebarCollapsed;
            localStorage.setItem('admin-sidebar-collapsed', this.sidebarCollapsed ? '1' : '0');
        }
    }"
    @keydown.escape.window="userMenuOpen = false"
>
    <div class="flex min-h-screen bg-gray-950 text-gray-100">
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

        <aside
            :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                sidebarCollapsed ? 'lg:admin-sidebar--collapsed' : '',
            ]"
            class="admin-sidebar fixed inset-y-0 left-0 z-50 flex h-screen max-h-screen shrink-0 flex-col border-r border-gray-800 bg-gray-900 transition-transform duration-200 lg:sticky lg:top-0 lg:translate-x-0"
        >
            <div class="admin-sidebar-header flex shrink-0 items-center gap-3 border-b border-gray-800 bg-gray-900 px-5 py-5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white p-1">
                    <img src="{{ asset('images/cean-mark.svg') }}" alt="{{ config('cean.acronym') }}" class="h-full w-full object-contain">
                </div>
                <div class="admin-sidebar-header-inner min-w-0 flex-1">
                    <p class="admin-sidebar-brand-text text-sm font-bold tracking-wide text-white">{{ config('cean.acronym') }}</p>
                    <span class="admin-sidebar-brand-text mt-1 inline-block rounded-full bg-cean-cyan/20 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-cean-cyan">
                        @if ($user->isEncargadoDocente())
                            Encargado-docente
                        @elseif ($user->isEncargado())
                            Encargado
                        @else
                            Administración
                        @endif
                    </span>
                    @if ($user->isEncargado() && $user->sede)
                        <span class="admin-sidebar-brand-text mt-1 block truncate text-[10px] text-gray-500">{{ $user->sede->nombre }}</span>
                    @endif
                </div>
                <button
                    type="button"
                    class="admin-sidebar-toggle hidden shrink-0 rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-800 hover:text-white lg:inline-flex"
                    @click="toggleSidebarCollapsed()"
                    :aria-label="sidebarCollapsed ? 'Expandir menú' : 'Contraer menú'"
                    :title="sidebarCollapsed ? 'Expandir menú' : 'Contraer menú'"
                >
                    <svg x-show="! sidebarCollapsed" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    <svg x-show="sidebarCollapsed" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </div>

            <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto overscroll-contain px-3 py-4" aria-label="Navegación administración">
                <x-admin.nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" icon="home">
                    Inicio
                </x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.alumnos')" :active="request()->routeIs('admin.alumnos')" icon="alumnos">
                    Alumnos
                </x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.grupos')" :active="request()->routeIs('admin.grupos')" icon="grupos">
                    Grupos
                </x-admin.nav-link>
                @if ($user->isAdmin())
                    <x-admin.nav-link :href="route('admin.materias')" :active="request()->routeIs('admin.materias*') || request()->routeIs('admin.licenciaturas*')" icon="materias">
                        Materias y Carreras
                    </x-admin.nav-link>
                @endif
                <x-admin.nav-link :href="route('admin.docentes.index')" :active="request()->routeIs('admin.docentes.*')" icon="docentes">
                    Docentes
                </x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.calificaciones.index')" :active="request()->routeIs('admin.calificaciones.*')" icon="calificaciones">
                    Calificaciones
                </x-admin.nav-link>
                @if ($user->isAdmin())
                    <x-admin.nav-link :href="route('admin.sedes.index')" :active="request()->routeIs('admin.sedes.*')" icon="sedes">
                        Sedes
                    </x-admin.nav-link>
                    <x-admin.nav-link :href="route('admin.usuarios.index')" :active="request()->routeIs('admin.usuarios.*')" icon="usuarios">
                        Usuarios
                    </x-admin.nav-link>
                @endif
                <x-admin.nav-link :href="route('admin.ciclos.index')" :active="request()->routeIs('admin.ciclos.*') || request()->routeIs('admin.periodos.*')" icon="ciclos">
                    Ciclos y periodos
                </x-admin.nav-link>
                @if ($user->isDocente())
                    <div class="admin-sidebar-brand-text border-t border-gray-800 pt-3 mt-3">
                        <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-wide text-gray-500">Portal docente</p>
                        <x-admin.nav-link :href="route('docente.dashboard')" :active="request()->routeIs('docente.*')" icon="docentes">
                            Mis materias y clases
                        </x-admin.nav-link>
                    </div>
                @endif
            </nav>

            <div class="admin-sidebar-footer shrink-0 border-t border-gray-800 bg-gray-900 p-4">
                <div class="admin-sidebar-footer-profile flex items-center gap-3">
                    <div class="relative shrink-0" @click.outside="userMenuOpen = false">
                        <button
                            type="button"
                            class="admin-user-avatar flex h-10 w-10 items-center justify-center rounded-full bg-cean-navy text-sm font-semibold text-white transition hover:ring-2 hover:ring-cean-cyan/50 focus:outline-none focus:ring-2 focus:ring-cean-cyan"
                            @click="userMenuOpen = ! userMenuOpen"
                            :aria-expanded="userMenuOpen"
                            aria-haspopup="true"
                            aria-label="Menú de cuenta"
                        >
                            {{ $iniciales }}
                        </button>

                        <div
                            x-show="userMenuOpen"
                            x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="admin-user-menu absolute bottom-full left-0 z-[70] mb-2 w-56 overflow-hidden rounded-xl border border-gray-700 bg-gray-800 py-1 shadow-xl"
                            role="menu"
                        >
                            <div class="border-b border-gray-700 px-4 py-3">
                                <p class="truncate text-sm font-medium text-white">{{ $user->name }}</p>
                                <p class="truncate text-xs text-gray-500">{{ $user->email }}</p>
                            </div>
                            <a
                                href="{{ route('profile.edit') }}"
                                class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-300 transition hover:bg-gray-700/80 hover:text-white"
                                role="menuitem"
                                @click="userMenuOpen = false"
                            >
                                <svg class="h-4 w-4 shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                Configurar cuenta
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-rose-400 transition hover:bg-gray-700/80 hover:text-rose-300"
                                    role="menuitem"
                                >
                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                    </svg>
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="admin-sidebar-footer-text min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-white">{{ $user->name }}</p>
                        <p class="truncate text-[10px] text-gray-500">{{ $user->email }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="admin-topbar flex items-center justify-between border-b border-gray-800 bg-gray-900/80 px-4 py-4 backdrop-blur sm:px-6 lg:px-8">
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
                        @if ($breadcrumb !== $title)
                            <p class="text-xs text-gray-500">
                                <span>Inicio</span>
                                <span class="mx-1">›</span>
                                <span class="text-gray-400">{{ $breadcrumb }}</span>
                            </p>
                        @endif
                        <h1 class="text-xl font-bold text-cean-cyan sm:text-2xl">{{ $title }}</h1>
                    </div>
                </div>

                <button type="button" class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-800 hover:text-cean-cyan" aria-label="Notificaciones">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.031A7.967 7.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a7.967 7.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.031m-5.714 0a24.255 24.255 0 0 1-1.155-.022m1.155.022a24.253 24.253 0 0 0 0-3.977m0 0a23.94 23.94 0 0 1-5.857-1.022M15 17.25h-3.75m-7.5 0h7.5m-7.5 0-1.5h9a1.5 1.5 0 0 0 1.5-1.5v-9a1.5 1.5 0 0 0-1.5-1.5h-9a1.5 1.5 0 0 0-1.5 1.5v9a1.5 1.5 0 0 0 1.5 1.5Z" />
                    </svg>
                </button>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>

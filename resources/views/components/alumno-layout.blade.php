@props([
    'title' => 'Portal alumno',
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
<body
    class="font-sans antialiased"
    x-data="{
        sidebarOpen: false,
        sidebarCollapsed: localStorage.getItem('alumno-sidebar-collapsed') === '1',
        userMenuOpen: false,
        toggleSidebarCollapsed() {
            this.sidebarCollapsed = ! this.sidebarCollapsed;
            localStorage.setItem('alumno-sidebar-collapsed', this.sidebarCollapsed ? '1' : '0');
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
                sidebarCollapsed ? 'admin-sidebar--collapsed' : '',
            ]"
            class="admin-sidebar alumno-sidebar fixed inset-y-0 left-0 z-50 flex h-screen max-h-screen shrink-0 flex-col border-r border-gray-800 bg-gray-900 transition-transform duration-200 lg:sticky lg:top-0 lg:translate-x-0"
        >
            <div class="admin-sidebar-header flex shrink-0 items-center gap-3 border-b border-gray-800 bg-gray-900 px-5 py-5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white p-1">
                    <img src="{{ asset('images/cean-mark.svg') }}" alt="{{ config('cean.acronym') }}" class="h-full w-full object-contain">
                </div>
                <div class="admin-sidebar-header-inner min-w-0 flex-1">
                    <p class="admin-sidebar-brand-text text-sm font-bold tracking-wide text-white">{{ config('cean.acronym') }}</p>
                    <span class="admin-sidebar-brand-text mt-1 inline-block rounded-full bg-emerald-500/20 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-400">
                        Alumno
                    </span>
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

            <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto overscroll-contain px-3 py-4" aria-label="Navegación alumno">
                <x-alumno.nav-link :href="route('alumno.dashboard')" :active="request()->routeIs('alumno.dashboard')" icon="home">
                    Inicio
                </x-alumno.nav-link>
                <x-alumno.nav-link :href="route('alumno.boleta')" :active="request()->routeIs('alumno.boleta')" icon="boleta">
                    Mi boleta
                </x-alumno.nav-link>
                <x-alumno.nav-link :href="route('alumno.kardex')" :active="request()->routeIs('alumno.kardex')" icon="kardex">
                    Historial académico
                </x-alumno.nav-link>
            </nav>

            <div class="admin-sidebar-footer shrink-0 border-t border-gray-800 bg-gray-900 p-4">
                <div class="admin-sidebar-footer-profile relative flex items-center gap-3" @click.outside="userMenuOpen = false">
                    <button
                        type="button"
                        class="flex min-w-0 flex-1 items-center gap-3 rounded-lg text-left transition hover:bg-gray-800/80 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                        @click="userMenuOpen = ! userMenuOpen"
                        :aria-expanded="userMenuOpen"
                        aria-haspopup="true"
                        aria-label="Menú de cuenta"
                    >
                        <span class="admin-user-avatar flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-cean-navy text-sm font-semibold text-white">
                            {{ $iniciales }}
                        </span>
                        <span class="admin-sidebar-footer-text min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium text-white">{{ $user->name }}</span>
                            @if ($user->alumno)
                                <span class="block truncate font-mono text-[10px] text-gray-500">{{ $user->alumno->matricula }}</span>
                            @else
                                <span class="block truncate text-[10px] text-gray-500">{{ $user->email }}</span>
                            @endif
                        </span>
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
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            Mi perfil
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
                        <h1 class="text-xl font-bold text-white sm:text-2xl">{{ $title }}</h1>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>

@props([
    'pageTitle' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle ? $pageTitle.' — ' : '' }}{{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500|montserrat:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="flex min-h-screen flex-col bg-gray-800 bg-cean-dots bg-cean-dots text-gray-100">
        <main class="flex flex-1 flex-col items-center justify-center px-4 py-10 sm:py-16">
            {{ $slot }}
        </main>

        <footer class="border-t border-gray-700/80 px-4 py-6 text-center">
            <p class="font-mono text-xs text-gray-500">
                &copy; {{ date('Y') }} {{ config('cean.title') }}
            </p>
        </footer>
    </div>
</body>
</html>

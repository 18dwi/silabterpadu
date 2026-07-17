<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link class="favicon" rel="icon" type="image/png" href="{{ asset('images/logo_poltekkes.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-50">
            <div class="flex flex-col items-center">
                <a href="/">
                    <x-application-logo class="w-44 h-44 object-contain" />
                </a>
                <h1 class="text-2xl font-bold text-teal-800 mt-4 tracking-wide text-center">Si-Lab Terpadu</h1>
                <p class="text-xs text-gray-550 font-bold mt-1.5 text-center uppercase tracking-wider">Keperawatan • Kebidanan • Kes. Gigi • Ortotik Prostetik</p>
                <p class="text-[10px] text-gray-450 font-medium mt-0.5 text-center uppercase tracking-wider">Poltekkes Kemenkes Jakarta I</p>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>

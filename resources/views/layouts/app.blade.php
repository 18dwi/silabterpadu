<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/logo_poltekkes.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @if(session()->has('original_user_id'))
                <div class="bg-amber-600 text-white font-bold py-2.5 px-4 text-center text-xs flex justify-between items-center z-50 sticky top-0 shadow-md">
                    <div class="flex items-center gap-1.5 justify-center w-full">
                        <span>⚠️ Anda sedang mengakses akun mahasiswa: <strong class="underline">{{ Auth::user()->name }}</strong> (NIM: {{ Auth::user()->nomor_induk }}).</span>
                        <form method="POST" action="{{ route('impersonate.leave') }}" class="inline ml-3">
                            @csrf
                            <button type="submit" class="bg-white text-amber-700 font-bold px-3 py-1 rounded hover:bg-amber-100 transition shadow-sm">
                                Kembali ke Admin
                            </button>
                        </form>
                    </div>
                </div>
            @endif
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>

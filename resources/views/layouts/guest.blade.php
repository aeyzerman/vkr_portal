<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="app-shell flex min-h-screen flex-col items-center justify-center px-4 py-10">
            <a href="{{ route('login') }}" class="mb-8">
                <x-application-logo class="h-14 w-auto fill-current text-stone-700" />
            </a>

            <div class="panel w-full max-w-md">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>

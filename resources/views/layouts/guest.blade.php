<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Baano') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased" style="background-color: #E8E6E1;">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">


<div>
    <a href="/" class="flex items-center justify-center">
        <img src="/images/logo.svg" alt="Baano" class="h-18 w-auto">
    </a>
</div>
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 glass" style="border-radius: 16px; backdrop-filter: blur(10px);">
            {{ $slot }}
        </div>
    </div>
</body>
</html>

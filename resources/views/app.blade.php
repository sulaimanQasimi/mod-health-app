<!DOCTYPE html>
@php
    $reactLocale = session('language', 'dr');
    $reactDirection = $reactLocale === 'en' ? 'ltr' : 'rtl';
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $reactDirection }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- Scripts -->
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    @inertiaHead
</head>
<body class="font-sans antialiased" dir="{{ $reactDirection }}">
    @inertia
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- TEMP FIX: avoid Vite crash --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body class="font-sans">

    <div class="min-h-screen flex items-center justify-center">
        @yield('content')
    </div>

</body>
</html>
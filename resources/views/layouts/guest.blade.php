<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'เข้าสู่ระบบ' }} - ระบบนิเทศการศึกษา</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sarabun:400,500,600,700|prompt:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Sarabun', 'Prompt', sans-serif;
        }
    </style>
</head>
<body class="h-full antialiased bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50">
    <!-- Mourning Ribbon -->
    <div class="mourning-ribbon">
        <svg viewBox="0 0 100 100" class="w-full h-full">
            <polygon points="0,0 100,0 0,100" fill="#1a1a1a"/>
            <g transform="translate(25, 25) rotate(-45)">
                <ellipse cx="10" cy="20" rx="8" ry="15" fill="#2a2a2a"/>
                <ellipse cx="20" cy="20" rx="8" ry="15" fill="#2a2a2a"/>
                <rect x="12" y="20" width="6" height="25" fill="#2a2a2a"/>
            </g>
        </svg>
    </div>

    <div class="min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Login Card -->
            <div class="bg-gradient-to-b from-pink-50 to-white rounded-3xl shadow-xl p-8 border border-pink-200">
                {{ $slot }}
            </div>
        </div>
    </div>

    <!-- Cookie Consent -->
    @include('components.cookie-consent')
</body>
</html>

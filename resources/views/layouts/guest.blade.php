<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'FNS Finance') }} — ເຂົ້າສູ່ລະບົບ</title>
        <link rel="icon" type="image/webp" href="{{ asset('storage/NUOL-Logo-192.webp') }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/NUOL-Logo-192.webp') }}">
        <link
            rel="preload"
            as="image"
            href="{{ asset('storage/BG-login-768.webp') }}"
            imagesrcset="{{ asset('storage/BG-login-768.webp') }} 768w, {{ asset('storage/BG-login-1280.webp') }} 1280w, {{ asset('storage/BG-login-1920.webp') }} 1920w"
            imagesizes="100vw"
            fetchpriority="high"
        >
        <link rel="preload" href="{{ Vite::asset('resources/fonts/NotoSansLao-Regular.ttf') }}" as="font" type="font/ttf" crossorigin>
        <link rel="preload" href="{{ Vite::asset('resources/fonts/NotoSansLao-Bold.ttf') }}" as="font" type="font/ttf" crossorigin>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="fns-login-bg">
            <div class="fns-login-card">
                <!-- Logo & Branding -->
                <div class="fns-login-logo-wrap">
                    <img src="{{ asset('storage/NUOL-Logo-192.webp') }}" alt="NUOL Logo" width="192" height="192" fetchpriority="high" decoding="async">
                    <div>
                        <div class="fns-login-title">FNS Finance</div>
                        <div class="fns-login-subtitle">ລະບົບບໍລິຫານການເງິນ · ມະຫາວິທະຍາໄລ</div>
                    </div>
                </div>
                <div class="fns-login-divider"></div>
                {{ $slot }}
            </div>
        </div>
    </body>
</html>

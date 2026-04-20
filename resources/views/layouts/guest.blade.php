<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ລະບົບງົບປະມານ FNS') }} - ເຂົ້າສູ່ລະບົບ</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body style="background: var(--color-bg-page); color: var(--color-text-primary); font-family: var(--font-sans); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;">
        
        <div style="width: 100%; max-width: 420px;">
            <div style="text-align: center; margin-bottom: 30px;">
                <img src="{{ asset('storage/logofns.jpg') }}" alt="FNS Logo" style="width: 80px; height: 80px; object-fit: contain; margin: 0 auto 16px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <h1 style="font-size: 24px; font-weight: 600; color: var(--color-text-primary); margin-bottom: 4px;">ມະຫາວິທະຍາໄລແຫ່ງຊາດ</h1>
                <p style="font-size: 16px; color: var(--color-text-secondary);">ລະບົບງົບປະມານ FNS</p>
            </div>

            <div style="background: var(--color-bg-white); border-radius: var(--radius-xl); box-shadow: var(--shadow-dropdown); padding: 32px; border: 1px solid var(--color-border);">
                {{ $slot }}
            </div>

            <div style="text-align: center; margin-top: 24px; font-size: var(--font-size-sm); color: var(--color-text-tertiary);">
                &copy; {{ date('Y') }} National University of Laos. All rights reserved.
            </div>
        </div>

    </body>
</html>

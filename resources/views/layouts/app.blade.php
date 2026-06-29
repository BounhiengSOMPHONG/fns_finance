<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'FNS Finance') }}</title>

    <link rel="icon" type="image/webp" href="{{ asset('storage/NUOL-Logo-192.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('storage/NUOL-Logo-192.webp') }}">
    @if (request()->routeIs('head_of_finance.home'))
        <link
            rel="preload"
            as="image"
            href="{{ asset('storage/BG-login-768.webp') }}"
            imagesrcset="{{ asset('storage/BG-login-768.webp') }} 768w, {{ asset('storage/BG-login-1280.webp') }} 1280w, {{ asset('storage/BG-login-1920.webp') }} 1920w"
            imagesizes="100vw"
        >
    @endif
    <link rel="preload" href="{{ Vite::asset('resources/fonts/NotoSansLao-Regular.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="preload" href="{{ Vite::asset('resources/fonts/NotoSansLao-Bold.ttf') }}" as="font" type="font/ttf" crossorigin>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased" style="background: var(--fns-gray-100);">
    <div style="display:flex; flex-direction:column; min-height:100vh;">
        <x-layouts.admin-header />
        <main id="admin-main" class="fns-main">
            {{ $slot }}
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.logout-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'ຢືນຢັນການອອກຈາກລະບົບ',
                        text: 'ທ່ານຕ້ອງການອອກຈາກລະບົບບໍ່?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'ຕົກລົງ',
                        cancelButtonText: 'ຍົກເລີກ',
                        reverseButtons: true,
                        confirmButtonColor: '#c9991a',
                        cancelButtonColor: '#6b7280'
                    }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });
        });
    </script>
</body>

</html>

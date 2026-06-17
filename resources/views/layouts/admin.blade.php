<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') — {{ config('app.name', 'FNS Finance') }}</title>

    <link rel="preload" href="{{ Vite::asset('resources/fonts/NotoSansLao-Regular.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="preload" href="{{ Vite::asset('resources/fonts/NotoSansLao-Bold.ttf') }}" as="font" type="font/ttf" crossorigin>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased" style="background: var(--fns-gray-100);">
    <div style="display:flex; flex-direction:column; min-height:100vh;">
        <x-layouts.admin-header />

        <main id="admin-main" class="fns-main">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="fns-alert fns-alert-success">
                    <svg style="width:16px;height:16px;flex-shrink:0;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="fns-alert fns-alert-error">
                    <svg style="width:16px;height:16px;flex-shrink:0;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Page Title --}}
            @hasSection('page-title')
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
                    <h1 class="fns-page-title">@yield('page-title')</h1>
                    @hasSection('page-title-actions')
                        <div style="margin-left:auto;display:flex;justify-content:flex-end;">
                            @yield('page-title-actions')
                        </div>
                    @endif
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')

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

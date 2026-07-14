<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ config('app.name', 'KelolaIN') }} |
        {{ ucwords(str_replace('.', ' ', Route::currentRouteName())) }}
    </title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Icon FAS & FAB -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="font-sans antialiased bg-[#F8FAFC] overflow-x-hidden">
    <div class="flex h-screen p-4 gap-4">

        <div class="w-60 shrink-0">
            @include('layouts.navigation')
        </div>


        <!-- Page Content -->
        <main class="flex-1 flex flex-col overflow-hidden">

            <!-- Page Heading -->
            @isset($header)
                <header>
                    {{ $header }}
                </header>
            @endisset

            <div class="h-full flex-1 overflow-y-auto overflow-x-hidden">
                {{ $slot }}
            </div>
        </main>
    </div>
    @stack('scripts')
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: "{{ session('success') }}",
                confirmButtonColor: '#2563eb'
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: "{{ session('error') }}",
                confirmButtonColor: '#dc2626'
            });
        </script>
    @endif

    @if (session('warning'))
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: "{{ session('warning') }}",
                confirmButtonColor: '#f59e0b'
            });
        </script>
    @endif

    @if (session('info'))
        <script>
            Swal.fire({
                icon: 'info',
                title: 'Informasi',
                text: "{{ session('info') }}",
                confirmButtonColor: '#2563eb'
            });
        </script>
    @endif
</body>

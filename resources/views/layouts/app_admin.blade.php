<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="SigniX - Dokumen Digital">
    <meta property="og:description" content="Sistem Pengesahan Dokumen Digital Dengan QR Code">
    <meta property="og:image" content="{{ asset('images/logo_signix.png') }}">
    <title>@yield('title')</title>

    @vite('resources/css/app.css')
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://unpkg.com/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">

</head>

<body class="bg-gray-50 font-sans">
    <div class="flex min-h-screen">
        @include('components.sidebar_ormawa') {{-- Sidebar di sebelah kiri --}}

        <div class="flex-1 flex flex-col w-full overflow-hidden">
            @include('components.navbar_ormawa') {{-- Topbar di atas --}}

            <main class="flex-1 p-6">
                @yield('content')
            </main>

        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>

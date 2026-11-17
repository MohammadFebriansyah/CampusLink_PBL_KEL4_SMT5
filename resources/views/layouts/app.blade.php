<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Beranda') - CampusLink</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
    @php
        $saya = auth()->user();
        $bolehDirektori = ! $saya || in_array($saya->role, ['mahasiswa', 'dosen'], true);
    @endphp
    @include('partials.nav')
    @include('partials.flash')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('partials.footer')
</body>
</html>

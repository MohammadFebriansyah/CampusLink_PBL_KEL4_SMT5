<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - CampusLink</title>
    @vite('resources/css/app.css')
</head>
<body class="flex min-h-screen items-center justify-center bg-linen px-4 py-12 sm:px-6">
    <main class="w-full max-w-xl rounded-card border border-fog bg-paper-white p-6 text-center shadow-subtle-3 sm:p-10" aria-labelledby="judul-galat">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-subheading font-bold text-navy">
            <span class="flex size-9 items-center justify-center rounded-xl bg-navy text-accent" aria-hidden="true">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/></svg>
            </span>
            <span>Campus<span class="text-accent">Link</span></span>
        </a>

        <p class="mt-10 text-display font-bold text-primary">@yield('code')</p>
        <h1 id="judul-galat" class="mt-4 text-heading-sm font-bold text-navy">@yield('title')</h1>
        <p class="mt-3 text-graphite">@yield('message')</p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            @yield('actions')
            <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke beranda</a>
        </div>
    </main>
</body>
</html>

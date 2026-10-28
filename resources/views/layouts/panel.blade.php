{{--
    Layout panel mitra: bar atas tipis, sidebar kiri tetap di layar lebar (256 px),
    drawer geser di layar sempit. Item aktif dihitung dari nama route.
    Panel admin memakai layouts.admin.
--}}
@php
    $saya = auth()->user();
    $kategori = request()->route('kategori');
    $aktifInfo = fn (string $pola, string $k) => request()->routeIs($pola) && $kategori === $k;

    $grupMenu = [
        '' => [
            ['Dashboard', route('mitra.dashboard'), request()->routeIs('mitra.dashboard'), null],
            ['Pendaftar', route('mitra.pendaftar.index'), request()->routeIs('mitra.pendaftar.*'), null],
            ['Kelola magang', route('mitra.informasi.index', 'magang'), $aktifInfo('mitra.informasi.*', 'magang'), null],
            ['Kelola lomba', route('mitra.informasi.index', 'lomba'), $aktifInfo('mitra.informasi.*', 'lomba'), null],
            ['Kelola beasiswa', route('mitra.informasi.index', 'beasiswa'), $aktifInfo('mitra.informasi.*', 'beasiswa'), null],
            ['Profil organisasi', route('mitra.profil.show'), request()->routeIs('mitra.profil.*'), null],
        ],
    ];
    $namaPanel = 'Panel mitra';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - CampusLink</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f8fc]"
      x-data="{ menu: false, lebar: window.matchMedia('(min-width: 1024px)').matches }"
      @resize.window.debounce.100ms="lebar = window.matchMedia('(min-width: 1024px)').matches; if (lebar) menu = false"
      @keydown.escape.window="if (menu) { menu = false; $refs.tombolMenu.focus() }"
      :class="menu && 'overflow-hidden'">
    {{-- TAMBAHAN: tautan lewati-ke-konten untuk pengguna keyboard. --}}
    <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[60] focus:rounded-input focus:bg-paper-white focus:px-3 focus:py-2 focus:text-sm focus:font-medium">Lewati ke konten</a>

    {{-- Bar atas: logo, tautan ke situs, nama pengguna, Keluar. --}}
    <header class="sticky top-0 z-40 flex h-14 items-center gap-2 border-b border-fog bg-paper-white px-4 sm:px-6">
        <button type="button" x-ref="tombolMenu" @click="menu = !menu"
                class="-ml-2 inline-flex size-10 shrink-0 items-center justify-center rounded-full hover:bg-mist lg:hidden"
                :aria-expanded="menu.toString()" aria-expanded="false" aria-controls="sidebar-panel" aria-label="Buka menu panel">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>

        <a href="{{ route($saya->panel()['rute']) }}" class="flex shrink-0 items-center gap-2 font-bold tracking-[-0.4px]">
            <span class="flex size-7 items-center justify-center rounded-lg bg-navy text-accent" aria-hidden="true">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/></svg>
            </span>
            <span class="text-navy">Campus<span class="text-accent">Link</span></span>
        </a>
        <span class="hidden rounded-full bg-mist px-2.5 py-0.5 text-caption font-medium text-graphite sm:inline">{{ $namaPanel }}</span>

        <div class="ml-auto flex min-w-0 items-center gap-1 sm:gap-3">
            <a href="{{ route('home') }}" class="inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-1.5 text-caption font-medium text-graphite hover:bg-mist hover:text-carbon sm:px-3">
                Lihat situs
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
            </a>
            <span class="hidden max-w-[14rem] truncate text-sm lg:max-w-[22rem] font-medium text-carbon md:inline" title="{{ $saya->name }}">{{ $saya->name }}</span>
            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button type="submit" class="btn btn-outline px-3 py-1.5">Keluar</button>
            </form>
        </div>
    </header>

    {{-- Latar gelap di belakang drawer (layar sempit). --}}
    <div x-cloak x-show="menu" x-transition.opacity @click="menu = false" class="fixed inset-0 z-40 bg-carbon/40 lg:hidden" aria-hidden="true"></div>

    <aside id="sidebar-panel"
           class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[85vw] -translate-x-full flex-col border-r border-fog bg-paper-white transition-transform duration-200 lg:top-14 lg:z-30 lg:w-64 lg:translate-x-0"
           :class="menu && 'translate-x-0!'" :inert="!lebar && !menu" aria-label="Menu {{ mb_strtolower($namaPanel) }}">
        <div class="flex h-14 shrink-0 items-center justify-between border-b border-fog px-4 lg:hidden">
            {{-- Di layar sempit nama pengguna tidak muat di bar atas, jadi ditampilkan di sini. --}}
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-carbon" title="{{ $saya->name }}">{{ $saya->name }}</p>
                <p class="text-caption text-graphite">{{ $namaPanel }}</p>
            </div>
            <button type="button" @click="menu = false; $refs.tombolMenu.focus()" class="inline-flex size-9 items-center justify-center rounded-full hover:bg-mist" aria-label="Tutup menu">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>
        <nav class="flex-1 overflow-y-auto px-3 py-4" aria-label="{{ $namaPanel }}">
            @foreach ($grupMenu as $judulGrup => $menu)
                <div class="{{ $loop->first ? '' : 'mt-5' }}">
                    @if ($judulGrup !== '')
                        <p class="px-3 pb-1.5 text-[11px] font-semibold tracking-[0.06em] text-ash uppercase">{{ $judulGrup }}</p>
                    @endif
                    <ul class="space-y-0.5">
                        @foreach ($menu as [$nama, $url, $aktif, $lencana])
                            <li>
                                <a href="{{ $url }}" @if ($aktif) aria-current="page" @endif
                                   class="relative flex items-center justify-between gap-2 rounded-input px-3 py-2 text-sm transition-colors {{ $aktif ? 'bg-primary/10 font-semibold text-primary before:absolute before:inset-y-1.5 before:left-0 before:w-1 before:rounded-full before:bg-accent' : 'font-medium text-graphite hover:bg-mist hover:text-carbon' }}">
                                    <span>{{ $nama }}</span>
                                    @if ($lencana)
                                        <span class="rounded-full bg-accent px-1.5 text-[11px] font-bold text-navy">{{ $lencana }}<span class="sr-only"> menunggu</span></span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>
    </aside>

    <div class="lg:pl-64">
        @include('partials.flash')
        <main id="konten" class="min-w-0">
            @yield('content')
        </main>
    </div>
</body>
</html>

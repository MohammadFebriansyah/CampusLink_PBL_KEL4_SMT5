{{--
    Layout panel admin: sidebar kiri (x-admin.sidebar), bar atas, dan isi halaman.
    Mendukung mode terang/gelap; pilihan tema dan sidebar diciutkan disimpan di localStorage.
    Halaman memakai @extends('layouts.admin') dengan section title dan content.
    Section heading (opsional) menggantikan jejak "Panel admin / judul" di bar atas dengan judul <h1>.
--}}
@php
    $saya = auth()->user();
    $mitraMenunggu = \App\Models\Mitra::where('status_verifikasi', \App\Models\Mitra::STATUS_PENDING)->count();
    $pendaftarMenunggu = \App\Support\Informasi::pendaftarMenunggu($saya);
    $notifikasi = array_filter([
        $mitraMenunggu > 0 ? [$mitraMenunggu . ' akun mitra menunggu verifikasi', 'Mitra belum bisa masuk sebelum disetujui.', route('admin.mitra.index'), 'shield-check'] : null,
        $pendaftarMenunggu > 0 ? [$pendaftarMenunggu . ' pendaftar kegiatan kampus', 'Menunggu ditinjau admin.', route('admin.pendaftar.index'), 'clipboard'] : null,
    ]);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - CampusLink</title>
    {{-- Dipasang sebelum CSS agar tema dan lebar sidebar sudah benar saat halaman pertama kali digambar (tidak berkedip). --}}
    <script>
        (() => {
            const akar = document.documentElement;
            let tema = null;
            let sidebar = null;
            try {
                tema = localStorage.getItem('campuslink-tema');
                sidebar = localStorage.getItem('campuslink-sidebar');
            } catch {}
            const gelap = tema ? tema === 'gelap' : window.matchMedia('(prefers-color-scheme: dark)').matches;
            akar.classList.toggle('dark', gelap);
            if (sidebar === 'tutup') akar.dataset.sidebar = 'tutup';
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background text-foreground"
      x-data="panelAdmin"
      @resize.window.debounce.100ms="ukur()"
      @keydown.escape.window="if (menu) { menu = false; $refs.tombolMenu.focus() }"
      :class="menu && 'overflow-hidden'">
    <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[60] focus:rounded-md focus:bg-card focus:px-3 focus:py-2 focus:text-sm focus:font-medium">Lewati ke konten</a>

    <x-admin.sidebar :mitra-menunggu="$mitraMenunggu" />

    {{-- Latar gelap di belakang drawer (layar sempit). --}}
    <div x-cloak x-show="menu" x-transition.opacity @click="menu = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden" aria-hidden="true"></div>

    {{-- min-w-0: tanpa ini kolom isi menolak menyempit di bawah lebar tabel terlebar dan mendorong halaman keluar layar. --}}
    <div class="flex min-h-screen min-w-0 flex-col transition-[padding] duration-200 lg:pl-64 ciut:pl-14">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-border bg-background px-4 sm:px-6">
            <button type="button" x-ref="tombolMenu" @click="alih()"
                    class="-ml-1.5 inline-flex size-9 shrink-0 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    :aria-expanded="terbuka.toString()" aria-expanded="false" aria-controls="sidebar-panel" aria-label="Buka atau tutup menu panel">
                <x-admin.icon name="panel-left" class="size-[18px]" />
            </button>

            @hasSection('heading')
                <h1 class="min-w-0 truncate text-lg font-semibold">@yield('heading')</h1>
            @else
                <p class="min-w-0 truncate text-sm">
                    <span class="hidden text-muted-foreground sm:inline">Panel admin <span aria-hidden="true">/</span> </span>
                    <span class="font-medium">@yield('title', 'Dashboard')</span>
                </p>
            @endif

            <div class="ml-auto flex shrink-0 items-center gap-1">
                {{-- Cari pengguna (memakai pencarian yang sudah ada di halaman Pengguna) --}}
                <div class="relative" x-data="{ buka: false }" @click.outside="buka = false" @keydown.escape.stop="buka = false; $refs.tombolCari.focus()">
                    <button type="button" x-ref="tombolCari" @click="buka = ! buka; if (buka) $fokus('cari-cepat')"
                            class="inline-flex size-9 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            :aria-expanded="buka.toString()" aria-expanded="false" aria-controls="panel-cari" aria-label="Cari pengguna">
                        <x-admin.icon name="search" class="size-[18px]" />
                    </button>
                    <form id="panel-cari" x-cloak x-show="buka" x-transition.origin.top.right method="GET" action="{{ route('admin.pengguna.index') }}" role="search"
                          class="fixed inset-x-4 top-16 mt-2 rounded-lg border border-border bg-card p-2 shadow-lg sm:absolute sm:inset-x-auto sm:top-auto sm:right-0 sm:w-80">
                        <label for="cari-cepat" class="sr-only">Cari nama atau email pengguna</label>
                        <div class="relative">
                            <x-admin.icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                            <input id="cari-cepat" name="q" type="search" maxlength="100" placeholder="Cari nama atau email pengguna"
                                   class="h-9 w-full rounded-md border border-input bg-background pr-3 pl-9 text-sm placeholder:text-muted-foreground focus:border-ring focus:outline-none">
                        </div>
                    </form>
                </div>

                {{-- Notifikasi: antrean yang menunggu admin --}}
                <div class="relative" x-data="{ buka: false }" @click.outside="buka = false" @keydown.escape.stop="buka = false; $refs.tombolNotif.focus()">
                    <button type="button" x-ref="tombolNotif" @click="buka = ! buka"
                            class="relative inline-flex size-9 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            :aria-expanded="buka.toString()" aria-expanded="false" aria-controls="panel-notifikasi"
                            aria-label="Notifikasi{{ count($notifikasi) ? ' (' . count($notifikasi) . ' baru)' : '' }}">
                        <x-admin.icon name="bell" class="size-[18px]" />
                        @if (count($notifikasi))
                            <span class="absolute top-2 right-2 size-2 rounded-full bg-accent ring-2 ring-background" aria-hidden="true"></span>
                        @endif
                    </button>
                    <div id="panel-notifikasi" x-cloak x-show="buka" x-transition.origin.top.right
                         class="fixed inset-x-4 top-16 mt-2 rounded-lg border border-border bg-card p-1 shadow-lg sm:absolute sm:inset-x-auto sm:top-auto sm:right-0 sm:w-80">
                        <p class="px-3 py-2 text-sm font-semibold">Notifikasi</p>
                        <div class="h-px bg-border"></div>
                        @forelse ($notifikasi as [$judul, $teks, $url, $ikon])
                            <a href="{{ $url }}" class="flex items-start gap-3 rounded-md px-3 py-2.5 hover:bg-muted">
                                <x-admin.icon :name="$ikon" class="mt-0.5 size-4 text-accent-dark dark:text-accent" />
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium">{{ $judul }}</span>
                                    <span class="block text-xs text-muted-foreground">{{ $teks }}</span>
                                </span>
                            </a>
                        @empty
                            <p class="px-3 py-6 text-center text-sm text-muted-foreground">Tidak ada yang perlu ditinjau.</p>
                        @endforelse
                    </div>
                </div>

                <x-admin.theme-toggle />

                {{-- Menu akun --}}
                <div class="relative ml-1" x-data="{ buka: false }" @click.outside="buka = false" @keydown.escape.stop="buka = false; $refs.tombolAkun.focus()">
                    <button type="button" x-ref="tombolAkun" @click="buka = ! buka"
                            class="flex items-center rounded-full ring-offset-2 ring-offset-background transition hover:ring-2 hover:ring-border"
                            :aria-expanded="buka.toString()" aria-expanded="false" aria-controls="menu-pengguna" aria-label="Menu akun {{ $saya->name }}">
                        <x-admin.avatar :name="$saya->name" class="size-8" />
                    </button>
                    <div id="menu-pengguna" x-cloak x-show="buka" x-transition.origin.top.right
                         class="absolute right-0 mt-2 w-60 rounded-lg border border-border bg-card p-1 shadow-lg">
                        <div class="px-3 py-2">
                            <p class="truncate text-sm font-semibold">{{ $saya->name }}</p>
                            <p class="truncate text-xs text-muted-foreground">{{ $saya->email }}</p>
                        </div>
                        <div class="my-1 h-px bg-border"></div>
                        <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium hover:bg-muted">
                            <x-admin.icon name="external" class="size-4" />
                            Lihat situs
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm font-medium text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                                <x-admin.icon name="logout" class="size-4" />
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main id="konten" class="mx-auto flex w-full max-w-7xl flex-1 flex-col p-4 sm:p-6">
            <x-admin.flash />
            @yield('content')
        </main>
    </div>
</body>
</html>

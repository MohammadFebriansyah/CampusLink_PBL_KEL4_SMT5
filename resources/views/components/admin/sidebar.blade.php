{{--
    Sidebar panel admin bergaya shadcn. Layar lebar: tetap di kiri, bisa diciutkan menjadi deret ikon
    (varian ciut:); layar sempit: drawer. State (menu, lebar) berasal dari Alpine.data('panelAdmin') di <body>.
    Item aktif dihitung dari nama route; halaman turunan ikut menandai menu induknya.
    Props: mitraMenunggu (jumlah mitra menunggu verifikasi, dihitung di layouts.admin)
--}}
@props(['mitraMenunggu' => 0])

@php
    $saya = auth()->user();
    $kategori = request()->route('kategori');
    $aktifInfo = fn (string $k) => request()->routeIs('admin.informasi.*') && $kategori === $k;

    // [nama, url, aktif, ikon, lencana]
    $grupMenu = [
        'Ringkasan' => [
            ['Dashboard', route('admin.dashboard'), request()->routeIs('admin.dashboard'), 'dashboard', null],
        ],
        'Verifikasi' => [
            ['Verifikasi mitra', route('admin.mitra.index'), request()->routeIs('admin.mitra.*'), 'shield-check', $mitraMenunggu],
            ['Pendaftar', route('admin.pendaftar.index'), request()->routeIs('admin.pendaftar.*'), 'clipboard', null],
        ],
        'Pengguna' => [
            ['Pengguna', route('admin.pengguna.index'), request()->routeIs('admin.pengguna.*'), 'users', null],
        ],
        'Konten' => [
            ['Kelola magang', route('admin.informasi.index', 'magang'), $aktifInfo('magang'), 'magang', null],
            ['Kelola lomba', route('admin.informasi.index', 'lomba'), $aktifInfo('lomba'), 'lomba', null],
            ['Kelola beasiswa', route('admin.informasi.index', 'beasiswa'), $aktifInfo('beasiswa'), 'beasiswa', null],
            ['Keahlian', route('admin.keahlian.index'), request()->routeIs('admin.keahlian.*'), 'star', null],
        ],
    ];
@endphp

<aside id="sidebar-panel"
       class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[85vw] -translate-x-full flex-col border-r border-border bg-sidebar transition-[translate,width] duration-200 lg:z-30 lg:w-64 lg:translate-x-0 ciut:w-14"
       :class="menu && 'translate-x-0!'" :inert="! lebar && ! menu" aria-label="Menu panel admin">
    {{-- Kepala: logo dan nama panel --}}
    <div class="flex h-16 shrink-0 items-center gap-2 px-3 ciut:px-2">
        <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 flex-1 items-center gap-2.5 rounded-md p-1.5 transition-colors hover:bg-sidebar-accent ciut:justify-center" title="CampusLink">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-navy text-accent ring-1 ring-white/10">
                <x-admin.icon name="link" class="size-4" />
            </span>
            <span class="min-w-0 leading-tight ciut:sr-only">
                <span class="block truncate text-sm font-semibold">Campus<span class="text-accent">Link</span></span>
                <span class="block truncate text-xs text-muted-foreground">Panel admin</span>
            </span>
        </a>
        <button type="button" @click="menu = false; $refs.tombolMenu.focus()" class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground hover:bg-sidebar-accent hover:text-foreground lg:hidden" aria-label="Tutup menu">
            <x-admin.icon name="x" class="size-4" />
        </button>
    </div>

    <nav class="flex-1 overflow-x-hidden overflow-y-auto px-3 py-2 ciut:px-2" aria-label="Panel admin">
        @foreach ($grupMenu as $judulGrup => $menu)
            <div class="{{ $loop->first ? '' : 'mt-4' }}">
                <p class="flex h-8 items-center px-2 text-xs font-medium text-muted-foreground ciut:sr-only">{{ $judulGrup }}</p>
                <ul class="space-y-1">
                    @foreach ($menu as [$nama, $url, $aktif, $ikon, $lencana])
                        <li>
                            <a href="{{ $url }}" @if ($aktif) aria-current="page" @endif title="{{ $nama }}"
                               class="relative flex h-8 items-center gap-2 rounded-md px-2 text-sm transition-colors ciut:justify-center {{ $aktif ? 'bg-sidebar-accent font-medium text-foreground' : 'text-muted-foreground hover:bg-sidebar-accent hover:text-foreground' }}">
                                <x-admin.icon :name="$ikon" class="size-4 {{ $aktif ? 'text-accent-dark dark:text-accent' : '' }}" />
                                <span class="flex-1 truncate ciut:sr-only">{{ $nama }}</span>
                                @if ($lencana)
                                    <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-accent px-1 text-xs font-semibold text-navy tabular-nums ciut:absolute ciut:-top-1 ciut:-right-1 ciut:h-4 ciut:min-w-4 ciut:text-[10px]">{{ $lencana }}<span class="sr-only"> menunggu</span></span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    {{-- Kaki: pengguna yang sedang masuk --}}
    <div class="border-t border-border p-3 ciut:p-2">
        <div class="flex items-center gap-2 rounded-md p-1.5 ciut:justify-center" title="{{ $saya->name }}">
            <x-admin.avatar :name="$saya->name" class="size-8" />
            <div class="min-w-0 leading-tight ciut:sr-only">
                <p class="truncate text-sm font-medium">{{ $saya->name }}</p>
                <p class="truncate text-xs text-muted-foreground">{{ $saya->email }}</p>
            </div>
        </div>
    </div>
</aside>

{{-- Header menempel di atas saat halaman di-scroll; bayangan muncul setelah mulai scroll. --}}
@php
    // Deret utama paling banyak 5 item: Beranda, Papan Informasi, Direktori, Kegiatan saya, menu akun.
    $kelasNav = fn (bool $aktif) => 'rounded-full px-4 py-2 text-sm font-medium tracking-[-0.32px] transition-colors '
        . ($aktif ? 'bg-primary/10 text-primary' : 'text-graphite hover:bg-mist hover:text-carbon');
    $kelasItem = fn (bool $aktif, string $ukuran = 'text-sm') => "flex items-center justify-between gap-3 rounded-input px-3 py-2 {$ukuran} font-medium transition-colors "
        . ($aktif ? 'bg-primary/10 text-primary' : 'text-carbon hover:bg-mist');
    $papan = [
        ['magang.index', 'magang.*', 'Magang'],
        ['lomba.index', 'lomba.*', 'Lomba'],
        ['beasiswa.index', 'beasiswa.*', 'Beasiswa'],
    ];
    $papanAktif = request()->routeIs('magang.*', 'lomba.*', 'beasiswa.*');
    $saya = auth()->user();
    $bolehDirektori = ! $saya || in_array($saya->role, ['mahasiswa', 'dosen'], true);
@endphp
<header x-data="{ scrolled: window.scrollY > 8 }" @scroll.window.passive="scrolled = window.scrollY > 8"
        :class="scrolled ? 'shadow-[0_6px_24px_-12px_rgb(15_30_80/0.25)]' : ''"
        class="sticky top-0 z-50 border-b border-fog bg-paper-white/90 backdrop-blur-md transition-shadow">
    <nav x-data="{ open: false }" @keydown.escape.window="open = false" class="mx-auto max-w-page px-4 sm:px-6" aria-label="Navigasi utama">
        <div class="flex h-16 items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-subheading font-bold tracking-[-0.4px]">
                <span class="flex size-8 items-center justify-center rounded-xl bg-navy text-accent shadow-[0_4px_12px_-4px_rgb(15_30_61/0.6)]" aria-hidden="true">
                    <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/></svg>
                </span>
                <span class="text-navy">Campus<span class="text-accent">Link</span></span>
            </a>

            <ul class="hidden items-center gap-1 md:flex">
                <li><a href="{{ route('home') }}" class="{{ $kelasNav(request()->routeIs('home')) }}" @if (request()->routeIs('home')) aria-current="page" @endif>Beranda</a></li>

                {{-- Papan Informasi: dropdown berbasis klik. Tutup dengan klik di luar atau Esc. --}}
                <li class="relative" x-data="{ buka: false }" @click.outside="buka = false"
                    @keydown.escape.window="if (buka) { buka = false; $refs.tombol.focus() }">
                    <button type="button" x-ref="tombol" @click="buka = !buka"
                            aria-haspopup="true" :aria-expanded="buka.toString()" aria-expanded="false" aria-controls="menu-papan"
                            class="inline-flex items-center gap-1 {{ $kelasNav($papanAktif) }}">
                        Papan Informasi
                        <svg class="size-4 transition-transform" :class="buka && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div id="menu-papan" x-cloak x-show="buka" x-transition.origin.top.left
                         class="absolute left-0 top-full mt-2 w-52 rounded-card border border-fog bg-paper-white p-1.5 shadow-subtle-3">
                        @foreach ($papan as [$rute, $pola, $nama])
                            @php $aktif = request()->routeIs($pola); @endphp
                            <a href="{{ route($rute) }}" class="{{ $kelasItem($aktif) }}" @if ($aktif) aria-current="page" @endif>
                                {{ $nama }}
                                @if ($aktif)<span class="size-1.5 rounded-full bg-accent" aria-hidden="true"></span>@endif
                            </a>
                        @endforeach
                    </div>
                </li>

                @if ($bolehDirektori)
                    <li><a href="{{ route('direktori.index') }}" class="{{ $kelasNav(request()->routeIs('direktori.*')) }}" @if (request()->routeIs('direktori.*')) aria-current="page" @endif>Direktori</a></li>
                @endif
                @if ($saya?->role === 'mahasiswa')
                    <li><a href="{{ route('kegiatan.index') }}" class="{{ $kelasNav(request()->routeIs('kegiatan.*')) }}" @if (request()->routeIs('kegiatan.*')) aria-current="page" @endif>Kegiatan saya</a></li>
                @endif
            </ul>

            <div class="flex items-center gap-1">
                @auth
                    @php
                        $panel = $saya->panel();
                        // Query langsung (bukan relasi $saya->mahasiswa) supaya selalu tepat satu query per halaman,
                        // tidak bergantung pada cache relasi di objek pengguna.
                        $profilSaya = match ($saya->role) {
                            'mahasiswa' => \App\Models\Mahasiswa::where('user_id', $saya->id)->first(),
                            'dosen' => \App\Models\Dosen::where('user_id', $saya->id)->first(),
                            default => null,
                        };
                        $permintaanBimbingan = $saya->role === 'dosen'
                            ? $saya->bimbinganMasuk()->where('status', 'menunggu')->count()
                            : 0;
                        $akunAktif = request()->routeIs('profil.*', 'bimbingan.*', 'mitra.profil.*');
                    @endphp

                    {{-- Menu akun: Profil, Bimbingan (dosen), Keluar. Dipakai di semua lebar layar. --}}
                    <div class="relative" x-data="{ buka: false }" @click.outside="buka = false"
                         @keydown.escape.window="if (buka) { buka = false; $refs.tombol.focus() }">
                        <button type="button" x-ref="tombol" @click="buka = !buka"
                                aria-haspopup="true" :aria-expanded="buka.toString()" aria-expanded="false" aria-controls="menu-akun"
                                aria-label="Menu akun {{ $saya->name }}"
                                class="flex items-center gap-2 rounded-full py-1 pr-1 pl-1 text-sm font-medium tracking-[-0.32px] transition hover:bg-mist sm:pr-3 {{ $akunAktif ? 'bg-primary/10 text-primary' : '' }}">
                            <span class="relative">
                                @if ($profilSaya)
                                    @include('direktori._avatar', ['profil' => $profilSaya, 'nama' => $saya->name, 'ukuran' => 'size-8', 'teks' => 'text-[11px]', 'cincin' => 'ring-2 ring-accent/60', 'alt' => ''])
                                @else
                                    <span class="flex size-8 items-center justify-center rounded-full bg-linear-to-br from-navy to-navy-2 text-[11px] font-bold text-accent ring-2 ring-accent/60" aria-hidden="true">{{ mb_strtoupper(mb_substr($saya->name, 0, 1)) }}</span>
                                @endif
                                {{-- TAMBAHAN: titik penanda di avatar bila ada permintaan bimbingan menunggu (lencana pindah ke dalam menu). --}}
                                @if ($permintaanBimbingan > 0)
                                    <span class="absolute -top-0.5 -right-0.5 size-2.5 rounded-full bg-accent ring-2 ring-paper-white" aria-hidden="true"></span>
                                @endif
                            </span>
                            <span class="hidden max-w-[10rem] truncate sm:inline" title="{{ $saya->name }}">{{ $saya->name }}</span>
                            <svg class="hidden size-4 shrink-0 transition-transform sm:block" :class="buka && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div id="menu-akun" x-cloak x-show="buka" x-transition.origin.top.right
                             class="absolute right-0 top-full mt-2 w-56 max-w-[calc(100vw-2rem)] rounded-card border border-fog bg-paper-white p-1.5 shadow-subtle-3">
                            <div class="px-3 pt-1.5 pb-2">
                                {{-- Nama lengkap di sini karena di tombol bisa terpotong elipsis. --}}
                                <p class="text-sm font-semibold break-words text-carbon">{{ $saya->name }}</p>
                                <p class="text-caption break-all text-graphite">{{ $saya->email }}</p>
                            </div>
                            @if ($saya->role === 'mitra' || $saya->role === 'admin')
                                {{-- Mitra dan admin: tautan ke dasbor panel masing-masing. --}}
                                <a href="{{ route($panel['rute']) }}" class="{{ $kelasItem(request()->routeIs($panel['rute'])) }}" @if (request()->routeIs($panel['rute'])) aria-current="page" @endif>{{ $panel['label'] }}</a>
                            @else
                                {{-- Mahasiswa dan dosen: dasbor panel (Profil, Kegiatan saya, Bimbingan ada di sidebar panel). --}}
                                <a href="{{ route('dasbor') }}" class="{{ $kelasItem(false) }}">Dasbor</a>
                            @endif
                            @if ($saya->role === 'mitra')
                                <a href="{{ route('mitra.profil.show') }}" class="{{ $kelasItem(request()->routeIs('mitra.profil.*')) }}" @if (request()->routeIs('mitra.profil.*')) aria-current="page" @endif>Profil</a>
                            @elseif ($saya->role !== 'admin')
                                <a href="{{ route('profil.show') }}" class="{{ $kelasItem(request()->routeIs('profil.*')) }}" @if (request()->routeIs('profil.*')) aria-current="page" @endif>Profil</a>
                            @endif
                            @if ($saya->role === 'dosen')
                                <a href="{{ route('bimbingan.index') }}" class="{{ $kelasItem(request()->routeIs('bimbingan.*')) }}" @if (request()->routeIs('bimbingan.*')) aria-current="page" @endif>
                                    Bimbingan
                                    @if ($permintaanBimbingan > 0)
                                        <span class="rounded-full bg-accent px-1.5 text-[11px] font-bold text-navy">{{ $permintaanBimbingan }}<span class="sr-only"> permintaan menunggu</span></span>
                                    @endif
                                </a>
                            @endif
                            <div class="my-1 border-t border-fog"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="{{ $kelasItem(false) }} w-full text-left">Keluar</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline hidden sm:inline-flex">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-register">Daftar</a>
                @endauth
                <button type="button" class="ml-1 inline-flex size-10 items-center justify-center rounded-full hover:bg-mist md:hidden"
                        @click="open = !open" :aria-expanded="open.toString()" aria-expanded="false" aria-controls="menu-ponsel" aria-label="Buka menu">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Menu layar sempit. Papan Informasi menjadi akordeon (terbuka otomatis bila salah satu halamannya aktif). --}}
        <div id="menu-ponsel" x-cloak x-show="open" x-transition.origin.top @click.outside="open = false" class="mb-3 rounded-card border border-fog bg-paper-white p-2 shadow-subtle-3 md:hidden">
            <a href="{{ route('home') }}" class="{{ $kelasItem(request()->routeIs('home'), 'text-body') }}" @if (request()->routeIs('home')) aria-current="page" @endif>Beranda</a>
            <div x-data="{ buka: @js($papanAktif) }">
                <button type="button" @click="buka = !buka" :aria-expanded="buka.toString()" aria-expanded="{{ $papanAktif ? 'true' : 'false' }}" aria-controls="akordeon-papan"
                        class="{{ $kelasItem($papanAktif, 'text-body') }} w-full text-left">
                    Papan Informasi
                    <svg class="size-4 transition-transform" :class="buka && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div id="akordeon-papan" x-show="buka" @unless ($papanAktif) x-cloak @endunless x-transition.opacity class="ml-3 border-l border-fog pl-2">
                    @foreach ($papan as [$rute, $pola, $nama])
                        @php $aktif = request()->routeIs($pola); @endphp
                        <a href="{{ route($rute) }}" class="{{ $kelasItem($aktif) }}" @if ($aktif) aria-current="page" @endif>
                            {{ $nama }}
                            @if ($aktif)<span class="size-1.5 rounded-full bg-accent" aria-hidden="true"></span>@endif
                        </a>
                    @endforeach
                </div>
            </div>
            @if ($bolehDirektori)
                <a href="{{ route('direktori.index') }}" class="{{ $kelasItem(request()->routeIs('direktori.*'), 'text-body') }}" @if (request()->routeIs('direktori.*')) aria-current="page" @endif>Direktori</a>
            @endif
            @if ($saya?->role === 'mahasiswa')
                <a href="{{ route('kegiatan.index') }}" class="{{ $kelasItem(request()->routeIs('kegiatan.*'), 'text-body') }}" @if (request()->routeIs('kegiatan.*')) aria-current="page" @endif>Kegiatan saya</a>
            @endif
            @guest
                <a href="{{ route('login') }}" class="{{ $kelasItem(false, 'text-body') }} sm:hidden">Masuk</a>
            @endguest
        </div>
    </nav>
</header>

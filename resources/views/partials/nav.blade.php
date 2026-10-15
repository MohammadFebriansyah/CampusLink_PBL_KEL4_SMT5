{{-- Header menempel di atas saat halaman di-scroll; bayangan muncul setelah mulai scroll. --}}
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
                <li><a href="{{ route('home') }}" class="rounded-full px-4 py-2 text-sm font-medium tracking-[-0.32px] transition-colors {{ request()->routeIs('home') ? 'bg-primary/10 text-primary' : 'text-graphite hover:bg-mist hover:text-carbon' }}">Beranda</a></li>
                <li><a href="{{ route('magang.index') }}" class="rounded-full px-4 py-2 text-sm font-medium tracking-[-0.32px] transition-colors {{ request()->routeIs('magang.*') ? 'bg-primary/10 text-primary' : 'text-graphite hover:bg-mist hover:text-carbon' }}">Magang</a></li>
                <li><a href="{{ route('lomba.index') }}" class="rounded-full px-4 py-2 text-sm font-medium tracking-[-0.32px] transition-colors {{ request()->routeIs('lomba.*') ? 'bg-primary/10 text-primary' : 'text-graphite hover:bg-mist hover:text-carbon' }}">Lomba</a></li>
                <li><a href="{{ route('beasiswa.index') }}" class="rounded-full px-4 py-2 text-sm font-medium tracking-[-0.32px] transition-colors {{ request()->routeIs('beasiswa.*') ? 'bg-primary/10 text-primary' : 'text-graphite hover:bg-mist hover:text-carbon' }}">Beasiswa</a></li>
                @if (! auth()->check() || in_array(auth()->user()->role, ['mahasiswa', 'dosen'], true))
                <li><a href="{{ route('direktori.index') }}" class="rounded-full px-4 py-2 text-sm font-medium tracking-[-0.32px] transition-colors {{ request()->routeIs('direktori.*') ? 'bg-primary/10 text-primary' : 'text-graphite hover:bg-mist hover:text-carbon' }}">Direktori</a></li>
                @endif
                @auth
                    @if (auth()->user()->role === 'mahasiswa')
                        <li><a href="{{ route('kegiatan.index') }}" class="rounded-full px-4 py-2 text-sm font-medium tracking-[-0.32px] transition-colors {{ request()->routeIs('kegiatan.*') ? 'bg-primary/10 text-primary' : 'text-graphite hover:bg-mist hover:text-carbon' }}">Kegiatan saya</a></li>
                    @elseif (auth()->user()->role === 'dosen')
                        @php $permintaanBimbingan = auth()->user()->bimbinganMasuk()->where('status', 'menunggu')->count(); @endphp
                        <li><a href="{{ route('bimbingan.index') }}" class="inline-flex items-center gap-1.5 rounded-full px-4 py-2 text-sm font-medium tracking-[-0.32px] transition-colors {{ request()->routeIs('bimbingan.*') ? 'bg-primary/10 text-primary' : 'text-graphite hover:bg-mist hover:text-carbon' }}">Bimbingan @if ($permintaanBimbingan > 0)<span class="rounded-full bg-accent px-1.5 text-[11px] font-bold text-navy">{{ $permintaanBimbingan }}</span>@endif</a></li>
                    @endif
                @endauth
            </ul>

            <div class="flex items-center gap-1">
                @auth
                    @php
                        $panel = auth()->user()->panel();
                    @endphp
                    @php
                        $saya = auth()->user();
                        // Query langsung (bukan relasi $saya->mahasiswa) supaya selalu tepat satu query per halaman,
                        // tidak bergantung pada cache relasi di objek pengguna.
                        $profilSaya = match ($saya->role) {
                            'mahasiswa' => \App\Models\Mahasiswa::where('user_id', $saya->id)->first(),
                            'dosen' => \App\Models\Dosen::where('user_id', $saya->id)->first(),
                            default => null,
                        };
                    @endphp
                    @if ($profilSaya)
                        {{-- Lingkaran foto profil (inisial bila belum ada foto). Di HP tampil tanpa tulisan. --}}
                        <a href="{{ route($panel['rute']) }}" title="Profil saya"
                           class="flex items-center gap-2 rounded-full py-1 pr-1 pl-1 text-sm font-medium tracking-[-0.32px] transition hover:bg-mist sm:pr-3 {{ request()->routeIs('profil.*') ? 'bg-primary/10 text-primary' : '' }}">
                            @include('direktori._avatar', ['profil' => $profilSaya, 'nama' => $saya->name, 'ukuran' => 'size-8', 'teks' => 'text-[11px]', 'cincin' => 'ring-2 ring-accent/60', 'alt' => 'Foto profil ' . $saya->name])
                            <span class="hidden sm:inline">{{ $panel['label'] }}</span>
                        </a>
                    @else
                        <a href="{{ route($panel['rute']) }}" class="hidden px-3 py-1.5 text-sm font-medium tracking-[-0.32px] hover:underline sm:inline-block">{{ $panel['label'] }}</a>
                    @endif
                    @if (auth()->user()->role === 'mitra')
                        <a href="{{ route('mitra.profil.show') }}" class="hidden px-3 py-1.5 text-sm font-medium tracking-[-0.32px] hover:underline sm:inline-block">Profil</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 text-sm font-medium tracking-[-0.32px] hover:underline">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline hidden sm:inline-flex">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-register">Daftar</a>
                @endauth
                <button type="button" class="ml-1 inline-flex size-10 items-center justify-center rounded-full hover:bg-mist md:hidden"
                        @click="open = !open" :aria-expanded="open" aria-label="Buka menu">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                </button>
            </div>
        </div>

        <div x-cloak x-show="open" x-transition.origin.top @click.outside="open = false" class="mb-3 rounded-card border border-fog bg-paper-white p-2 shadow-subtle-3 md:hidden">
            <a href="{{ route('home') }}" class="block rounded-input px-3 py-2 font-medium hover:bg-mist">Beranda</a>
            <a href="{{ route('magang.index') }}" class="block rounded-input px-3 py-2 font-medium hover:bg-mist">Magang</a>
            <a href="{{ route('lomba.index') }}" class="block rounded-input px-3 py-2 font-medium hover:bg-mist">Lomba</a>
            <a href="{{ route('beasiswa.index') }}" class="block rounded-input px-3 py-2 font-medium hover:bg-mist">Beasiswa</a>
            @if (! auth()->check() || in_array(auth()->user()->role, ['mahasiswa', 'dosen'], true))
            <a href="{{ route('direktori.index') }}" class="block rounded-input px-3 py-2 font-medium hover:bg-mist">Direktori</a>
            @endif
            @auth
                @if (auth()->user()->role === 'mahasiswa')
                    <a href="{{ route('kegiatan.index') }}" class="block rounded-input px-3 py-2 font-medium hover:bg-mist">Kegiatan saya</a>
                @elseif (auth()->user()->role === 'dosen')
                    <a href="{{ route('bimbingan.index') }}" class="block rounded-input px-3 py-2 font-medium hover:bg-mist">Bimbingan</a>
                @endif
            @endauth
            @auth
                <a href="{{ route($panel['rute']) }}" class="block rounded-input px-3 py-2 font-medium hover:bg-mist">{{ $panel['label'] }}</a>
                @if (auth()->user()->role === 'mitra')
                    <a href="{{ route('mitra.profil.show') }}" class="block rounded-input px-3 py-2 font-medium hover:bg-mist">Profil</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full rounded-input px-3 py-2 text-left font-medium hover:bg-mist">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block rounded-input px-3 py-2 font-medium hover:bg-mist">Masuk</a>
            @endauth
        </div>
    </nav>
</header>

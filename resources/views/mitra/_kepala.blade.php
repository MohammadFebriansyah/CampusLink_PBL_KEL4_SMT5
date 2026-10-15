{{--
    Kepala halaman mitra: pita navy, judul, dan menu mitra.
    Parameter: judul, deskripsi (opsional), aktif (dashboard|pendaftar|magang|lomba|beasiswa|profil; null = tanpa menu),
               kembali (opsional, [url, teks]), aksi (opsional, HTML tombol di kanan judul), atas (opsional, teks kecil di atas judul)
--}}
@php
    $menu = [
        'dashboard' => ['Dasbor', route('mitra.dashboard')],
        'pendaftar' => ['Pendaftar', route('mitra.pendaftar.index')],
        'magang' => ['Magang', route('mitra.informasi.index', 'magang')],
        'lomba' => ['Lomba', route('mitra.informasi.index', 'lomba')],
        'beasiswa' => ['Beasiswa', route('mitra.informasi.index', 'beasiswa')],
        'profil' => ['Profil organisasi', route('mitra.profil.show')],
    ];
@endphp
<section class="relative isolate overflow-hidden bg-navy text-paper-white">
    <div class="hero-dots pointer-events-none absolute inset-0" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -top-32 right-[-5%] size-[420px] rounded-full bg-primary/50 blur-3xl" aria-hidden="true"></div>

    <div class="relative mx-auto max-w-page px-4 pt-8 pb-8 sm:px-6 md:pt-10">
        @if (! empty($kembali))
            <a href="{{ $kembali[0] }}" class="inline-flex items-center gap-1 text-sm font-medium text-white/70 hover:text-paper-white">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 6-6 6 6 6"/></svg>
                {{ $kembali[1] }}
            </a>
        @else
            <p class="text-sm font-semibold text-accent">{{ $atas ?? 'Panel mitra' }}</p>
        @endif

        <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
            <div class="min-w-0">
                <h1 class="text-heading-sm font-bold tracking-[-0.5px] text-balance sm:text-heading">{{ $judul }}</h1>
                @if (! empty($deskripsi))
                    <p class="mt-1 text-white/70">{{ $deskripsi }}</p>
                @endif
            </div>
            @if (! empty($aksi))
                <div class="shrink-0">{!! $aksi !!}</div>
            @endif
        </div>

        @if (! empty($aktif))
            <nav class="mt-6 -mx-1 overflow-x-auto px-1 pb-1" aria-label="Menu mitra">
                <div class="inline-flex gap-1 rounded-full bg-white/10 p-1 ring-1 ring-white/15">
                    @foreach ($menu as $kunci => [$nama, $url])
                        <a href="{{ $url }}"
                           class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold whitespace-nowrap transition {{ $aktif === $kunci ? 'bg-accent text-navy' : 'text-white/75 hover:bg-white/10 hover:text-paper-white' }}"
                           @if ($aktif === $kunci) aria-current="page" @endif>{{ $nama }}</a>
                    @endforeach
                </div>
            </nav>
        @endif
    </div>
</section>

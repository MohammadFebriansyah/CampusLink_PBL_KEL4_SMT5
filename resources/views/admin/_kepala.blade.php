{{--
    Kepala halaman admin: pita navy, judul, penjelasan, dan menu admin.
    Parameter: judul, deskripsi (opsional), aktif (kunci menu; null = menu tidak ditampilkan),
               kembali (opsional, [url, teks]), aksi (opsional, HTML tombol di kanan judul)
--}}
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
            <p class="text-sm font-semibold text-accent">Panel admin</p>
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
            @include('admin._menu', ['aktif' => $aktif, 'gelap' => true])
        @endif
    </div>
</section>

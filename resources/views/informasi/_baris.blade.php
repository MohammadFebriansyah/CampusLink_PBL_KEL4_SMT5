{{-- Satu kartu hasil. Parameter: item, kategori. Harus berada di dalam x-data="panelDetail". --}}
@php
    $sisa = \App\Support\Tanggal::sisaHari($item->batas_pendaftaran);
    // Warna tenggat: merah bila tinggal seminggu, oranye bila sebulan, abu-abu bila masih lama.
    $warnaSisa = match (true) {
        $sisa <= 7 => 'bg-[#fee2e2] text-[#b91c1c]',
        $sisa <= 30 => 'bg-accent-wash text-accent-dark',
        default => 'bg-mist text-graphite',
    };
    $inisial = mb_strtoupper(collect(preg_split('/\s+/', trim(preg_replace('/^(PT|CV|UD)\.?\s+/i', '', $item->penyelenggara))))
        ->filter()->take(2)->map(fn ($kata) => mb_substr($kata, 0, 1))->join(''));
@endphp
<a href="{{ route($kategori . '.show', $item) }}"
   @click="buka($event, {{ $item->id }})"
   :class="aktif === {{ $item->id }} ? 'lg:border-accent lg:ring-2 lg:ring-accent/30 lg:shadow-[0_18px_40px_-24px_rgb(15_30_61/0.45)]' : ''"
   :aria-current="aktif === {{ $item->id }} ? 'true' : null"
   class="group flex gap-4 rounded-2xl border border-fog bg-paper-white p-5 transition lg:gap-3 lg:p-4 hover:-translate-y-0.5 hover:border-accent/40 hover:shadow-[0_18px_40px_-24px_rgb(15_30_61/0.45)] sm:p-6">
    <span class="hidden size-12 shrink-0 items-center justify-center rounded-xl bg-navy text-sm font-bold text-accent sm:flex lg:size-10 lg:text-xs" aria-hidden="true">{{ $inisial ?: '?' }}</span>

    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-2">
            <div class="min-w-0">
                <h2 class="text-subheading font-semibold text-carbon transition-colors group-hover:text-accent-dark lg:text-body lg:leading-snug">{{ $item->judul }}</h2>
                <p class="mt-1 text-sm text-graphite lg:mt-0.5 lg:text-caption">{{ $item->penyelenggara }}</p>
            </div>
            <span class="shrink-0 rounded-full px-3 py-1 text-caption font-semibold lg:px-2.5 lg:py-0.5 {{ $warnaSisa }}">{{ \App\Support\Tanggal::labelSisa($item->batas_pendaftaran) }}</span>
        </div>

        <p class="mt-3 line-clamp-2 text-graphite lg:mt-2 lg:line-clamp-1 lg:text-sm">{{ mb_strimwidth($item->deskripsi, 0, 200, '...') }}</p>

        <div class="mt-4 flex flex-wrap items-center gap-2 text-caption text-graphite lg:mt-3 lg:gap-1.5">
            @if ($kategori === 'magang')
                <span class="inline-flex items-center gap-1.5 rounded-full bg-mist px-3 py-1 font-medium lg:px-2.5 lg:py-0.5">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                    {{ $item->lokasi ?? 'Lokasi belum ditentukan' }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-mist px-3 py-1 font-medium lg:px-2.5 lg:py-0.5">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                    {{ \App\Support\Tanggal::rentang($item->periode_mulai, $item->periode_selesai) }}
                </span>
            @elseif ($kategori === 'lomba')
                <span class="inline-flex items-center gap-1.5 rounded-full bg-mist px-3 py-1 font-medium lg:px-2.5 lg:py-0.5">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4Z"/></svg>
                    Tingkat {{ ucfirst($item->tingkat) }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-mist px-3 py-1 font-medium lg:px-2.5 lg:py-0.5">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                    {{ \App\Support\Tanggal::rentang($item->tanggal_mulai, $item->tanggal_selesai) }}
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-mist px-3 py-1 font-medium lg:px-2.5 lg:py-0.5">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                    Batas {{ \App\Support\Tanggal::panjang($item->batas_pendaftaran) }}
                </span>
            @endif

            @if ($item->lewatCampuslink())
                <span class="inline-flex items-center gap-1 rounded-full bg-primary-wash px-3 py-1 font-semibold text-primary lg:px-2.5 lg:py-0.5">Daftar di CampusLink</span>
            @else
                <span class="inline-flex items-center gap-1 rounded-full border border-fog px-3 py-1 font-medium lg:px-2.5 lg:py-0.5">Situs penyelenggara ↗</span>
            @endif

            <span class="ml-auto hidden items-center gap-1 text-sm font-semibold text-accent-dark opacity-0 transition group-hover:opacity-100 sm:inline-flex lg:hidden">
                Lihat detail
                <svg class="size-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
            </span>
        </div>
    </div>
</a>

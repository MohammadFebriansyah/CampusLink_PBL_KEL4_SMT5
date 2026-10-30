{{--
    Satu kartu hasil, dirender Alpine dari InformasiResource. Dipakai di dalam <template x-for="item in items">
    yang berada di dalam x-data="panelDetail(...)" (untuk aktif dan buka()).
--}}
@php
    $ikonRincian = [
        'lokasi' => '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/>',
        'kalender' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'piala' => '<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4Z"/>',
    ];
@endphp
<a :href="item.url"
   @click="buka($event, item.id)"
   :class="aktif === item.id ? 'lg:border-foreground/30 lg:ring-2 lg:ring-accent/40' : ''"
   :aria-current="aktif === item.id ? 'true' : null"
   class="group flex gap-3 rounded-xl border border-border bg-card p-4 text-foreground shadow-sm transition hover:border-foreground/20 hover:shadow-md">
    <span class="hidden sm:block"><x-admin.avatar square class="size-10" x-text="item.inisial" /></span>

    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-2">
            <div class="min-w-0">
                <h2 class="font-semibold leading-snug group-hover:underline" x-text="item.judul"></h2>
                <p class="mt-0.5 text-xs text-muted-foreground" x-text="item.penyelenggara"></p>
            </div>
            {{-- Warna tenggat: merah bila tinggal seminggu, kuning bila sebulan, abu-abu bila masih lama. --}}
            <x-admin.badge class="shrink-0" ::data-varian="{ dekat: 'danger', sedang: 'warning' }[item.sisa.varian] ?? 'neutral'"><span x-text="item.sisa.label"></span></x-admin.badge>
        </div>

        <p class="mt-2 line-clamp-2 text-sm text-muted-foreground lg:line-clamp-1" x-text="item.ringkasan"></p>

        <div class="mt-3 flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground">
            <template x-for="r in item.rincian" :key="r.ikon + r.teks">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-0.5 font-medium">
                    @foreach ($ikonRincian as $kunci => $jalur)
                        <svg x-show="r.ikon === '{{ $kunci }}'" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $jalur !!}</svg>
                    @endforeach
                    <span x-text="r.teks"></span>
                </span>
            </template>

            <x-admin.badge variant="info" x-show="item.lewat_campuslink">Daftar di CampusLink</x-admin.badge>
            <x-admin.badge variant="outline" x-show="! item.lewat_campuslink">Situs penyelenggara ↗</x-admin.badge>
        </div>
    </div>
</a>

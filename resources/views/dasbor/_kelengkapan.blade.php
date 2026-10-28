{{-- Kartu kelengkapan profil di dasbor mahasiswa dan dosen. Parameter: kelengkapan (daftar [teks, selesai]) --}}
@php
    $selesai = count(array_filter(array_column($kelengkapan, 1)));
    $persen = (int) round($selesai / max(count($kelengkapan), 1) * 100);
@endphp
<x-admin.card title="Kelengkapan profil" description="Profil yang lengkap lebih mudah ditemukan di direktori">
    <x-slot:actions>
        <x-admin.button variant="ghost" size="sm" :href="route('profil.show')">Ubah profil</x-admin.button>
    </x-slot:actions>

    <div class="flex items-center gap-3">
        <x-admin.progress :value="$persen" label="Kelengkapan profil" />
        <span class="shrink-0 text-sm font-semibold tabular-nums">{{ $persen }}%</span>
    </div>
    <ul class="mt-5 flex flex-col gap-2.5">
        @foreach ($kelengkapan as [$teks, $ok])
            <li class="flex items-center gap-2.5 text-sm">
                @if ($ok)
                    <x-admin.icon name="check" class="size-4 text-emerald-600 dark:text-emerald-400" />
                    <span>{{ $teks }}</span>
                @else
                    <span class="size-4 shrink-0 rounded-full border-2 border-dashed border-input" aria-hidden="true"></span>
                    <span class="text-muted-foreground">{{ $teks }} <span class="sr-only">(belum)</span></span>
                @endif
            </li>
        @endforeach
    </ul>
</x-admin.card>

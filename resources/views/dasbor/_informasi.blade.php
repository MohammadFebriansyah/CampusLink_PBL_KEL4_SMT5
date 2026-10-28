{{-- Informasi yang masih dibuka dengan batas pendaftaran terdekat (dasbor mahasiswa dan dosen). Parameter: informasi --}}
<x-admin.card title="Batas pendaftaran terdekat" description="Magang, lomba, dan beasiswa yang masih dibuka" flush class="overflow-hidden">
    <x-slot:actions>
        <x-admin.button variant="ghost" size="sm" :href="route('magang.index')">Jelajahi</x-admin.button>
    </x-slot:actions>

    @if ($informasi->isEmpty())
        <x-admin.empty-state title="Belum ada informasi yang dibuka." icon="file" class="py-10" />
    @else
        <x-admin.table class="min-w-[40rem]">
            <x-slot:head>
                <th scope="col">Judul</th>
                <th scope="col">Kategori</th>
                <th scope="col">Batas</th>
                <th scope="col"><span class="sr-only">Aksi</span></th>
            </x-slot:head>
            @foreach ($informasi as $item)
                @php
                    $kategori = $item->kategori();
                    $sisa = \App\Support\Tanggal::sisaHari($item->batas_pendaftaran);
                @endphp
                <tr>
                    <td>
                        <p class="font-medium">{{ $item->judul }}</p>
                        <p class="text-muted-foreground">{{ $item->penyelenggara }}</p>
                    </td>
                    <td>
                        <span class="flex items-center gap-1.5 text-muted-foreground">
                            <x-admin.icon :name="$kategori" class="size-4" />
                            {{ \App\Support\Informasi::LABEL[$kategori] }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap">
                        <p>{{ \App\Support\Tanggal::panjang($item->batas_pendaftaran) }}</p>
                        <x-admin.badge :variant="$sisa <= 7 ? 'warning' : 'outline'" class="mt-1">{{ \App\Support\Tanggal::labelSisa($item->batas_pendaftaran) }}</x-admin.badge>
                    </td>
                    <td class="text-right">
                        <x-admin.button variant="ghost" size="sm" :href="route($kategori . '.show', $item->id)">Lihat</x-admin.button>
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif
</x-admin.card>

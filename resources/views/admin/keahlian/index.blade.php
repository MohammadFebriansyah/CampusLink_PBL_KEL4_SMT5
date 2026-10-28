@extends('layouts.admin')

@section('title', 'Keahlian')

@section('content')
    <x-admin.page-header title="Bidang keahlian" description="Daftar yang bisa dipilih mahasiswa dan dosen di profil, dan dipakai sebagai filter direktori." />

    <x-daftar.data id="data-keahlian" :nilai="$awal" />

    <div class="max-w-4xl space-y-6" x-data="daftar({ sumber: 'data-keahlian' })">
        <x-admin.card>
            <form method="POST" action="{{ route('admin.keahlian.store') }}" class="flex flex-col gap-3 sm:flex-row sm:items-start">
                @csrf
                <div class="flex-1">
                    @include('partials.field', ['name' => 'nama', 'label' => 'Tambah keahlian', 'hint' => 'Contoh: Desain Produk'])
                </div>
                <x-admin.button type="submit" class="sm:mt-7"><x-admin.icon name="plus" /> Tambah</x-admin.button>
            </form>
        </x-admin.card>

        <x-admin.card flush class="overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-border px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm font-medium" aria-live="polite" x-text="`${meta.total} keahlian`">{{ $awal['meta']['total'] }} keahlian</p>
                <form @submit.prevent="muat(1)" role="search" class="relative sm:w-64">
                    <x-admin.icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                    <label for="q" class="sr-only">Cari keahlian</label>
                    <input id="q" type="search" maxlength="100" x-model="filter.q" value="{{ $awal['filter']['q'] }}" placeholder="Cari keahlian" autocomplete="off"
                           class="h-9 w-full rounded-md border border-input bg-card pr-3 pl-9 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none">
                </form>
            </div>
            <x-daftar.status class="px-4" />
            <div class="transition-opacity" :class="memuat && 'opacity-60'" :aria-busy="memuat.toString()">
                <x-admin.table>
                    <x-slot:head>
                        <x-daftar.th kunci="nama">Nama</x-daftar.th>
                        <x-daftar.th kunci="dipakai" arah="desc">Dipakai</x-daftar.th>
                        <th scope="col"><span class="sr-only">Aksi</span></th>
                    </x-slot:head>

                    <template x-for="k in items" :key="k.id">
                        <tr>
                            <td class="font-medium" x-text="k.nama"></td>
                            <td class="text-muted-foreground" x-text="k.dipakai"></td>
                            <td>
                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                    <x-admin.button variant="ghost" size="sm" href="#" ::href="k.url.ubah">Ubah</x-admin.button>
                                    <x-admin.action x-url="k.url.hapus" method="DELETE" variant="danger-outline" x-confirm="k.konfirmasi_hapus" yes="Ya, hapus">Hapus</x-admin.action>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="items.length === 0" @if (count($awal['data'])) x-cloak @endif>
                        <td colspan="3">
                            <x-admin.empty-state icon="star" :title="$awal['filter']['q'] !== '' ? 'Tidak ada keahlian yang cocok.' : 'Belum ada keahlian.'"
                                                 x-title="adaFilter ? 'Tidak ada keahlian yang cocok.' : 'Belum ada keahlian.'" />
                        </td>
                    </tr>
                </x-admin.table>
            </div>
        </x-admin.card>

        <x-daftar.halaman :meta="$awal['meta']" />
    </div>
@endsection

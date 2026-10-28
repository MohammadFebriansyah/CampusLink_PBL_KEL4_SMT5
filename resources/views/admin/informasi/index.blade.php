@extends('layouts.admin')

@section('title', 'Kelola ' . mb_strtolower($label))

@section('content')
    @php
        $filter = $awal['filter'];
    @endphp

    <x-admin.page-header :title="'Informasi ' . mb_strtolower($label)"
                         :description="'Semua informasi ' . mb_strtolower($label) . ' dari mitra dan admin, termasuk yang ditutup dan disembunyikan.'">
        <x-slot:actions>
            <x-admin.button :href="route('admin.informasi.create', $kategori)"><x-admin.icon name="plus" /> Tambah {{ mb_strtolower($label) }}</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-daftar.data id="data-informasi" :nilai="$awal" />

    <div x-data="daftar({ sumber: 'data-informasi' })">
        <form @submit.prevent="muat(1)" role="search" class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <x-admin.icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <label for="q" class="sr-only">Judul atau penyelenggara</label>
                <input id="q" type="search" maxlength="100" x-model="filter.q" value="{{ $filter['q'] }}" placeholder="Cari judul atau penyelenggara" autocomplete="off"
                       class="h-10 w-full rounded-md border border-input bg-card pr-3 pl-9 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none">
            </div>
            <x-admin.select name="tayang" label="Status tayang" placeholder="Semua status" :selected="$filter['tayang']" x-model="filter.tayang"
                            :options="['dibuka' => 'Dibuka', 'ditutup' => 'Ditutup', 'disembunyikan' => 'Disembunyikan']" />
        </form>

        @if ($errors->any())
            <p class="mt-3 text-sm text-red-600 dark:text-red-400" role="alert">{{ $errors->first() }}</p>
        @endif
        <x-daftar.status />

        <x-admin.card flush class="mt-6 overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b border-border px-4 py-3">
                <p class="text-sm font-medium" aria-live="polite" x-text="`${meta.total} informasi`">{{ $awal['meta']['total'] }} informasi</p>
                <button type="button" x-cloak x-show="adaFilter" @click="aturUlang()" class="text-sm font-medium text-muted-foreground hover:text-foreground">Atur ulang</button>
            </div>
            <div class="transition-opacity" :class="memuat && 'opacity-60'" :aria-busy="memuat.toString()">
                <x-admin.table class="min-w-[52rem]">
                    <x-slot:head>
                        <x-daftar.th kunci="judul">Judul</x-daftar.th>
                        <x-daftar.th kunci="batas">Batas</x-daftar.th>
                        <x-daftar.th kunci="pendaftar" arah="desc">Pendaftar</x-daftar.th>
                        <th scope="col">Status</th>
                        <th scope="col"><span class="sr-only">Aksi</span></th>
                    </x-slot:head>

                    <template x-for="item in items" :key="item.id">
                        <tr>
                            <td>
                                <p class="font-medium" x-text="item.judul"></p>
                                <p class="text-muted-foreground" x-text="`${item.penyelenggara}, ditambahkan oleh ${item.pembuat}`"></p>
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    <x-admin.badge ::data-varian="item.jenis.varian"><span x-text="item.jenis.label"></span></x-admin.badge>
                                </div>
                            </td>
                            <td class="whitespace-nowrap text-muted-foreground" x-text="item.batas"></td>
                            <td class="text-muted-foreground tabular-nums" x-text="item.pendaftar ?? '-'"></td>
                            <td><x-admin.badge ::data-varian="item.status.varian" dot><span x-text="item.status.label"></span></x-admin.badge></td>
                            <td>
                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                    <template x-if="item.url.lihat">
                                        <x-admin.button variant="ghost" size="sm" href="#" ::href="item.url.lihat">Lihat</x-admin.button>
                                    </template>
                                    <x-admin.button variant="ghost" size="sm" href="#" ::href="item.url.ubah">Ubah</x-admin.button>
                                    <template x-if="item.status.kunci === 'disembunyikan'">
                                        <x-admin.action x-url="item.url.tampilkan">Tampilkan</x-admin.action>
                                    </template>
                                    <template x-if="item.status.kunci !== 'disembunyikan'">
                                        <x-admin.action x-url="item.url.sembunyikan" confirm="Sembunyikan?">Sembunyikan</x-admin.action>
                                    </template>
                                    <x-admin.action x-url="item.url.hapus" method="DELETE" variant="danger-outline" confirm="Hapus permanen?" yes="Ya, hapus">Hapus</x-admin.action>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="items.length === 0" @if (count($awal['data'])) x-cloak @endif>
                        <td colspan="5">
                            <x-admin.empty-state :title="$filter['q'] !== '' || $filter['tayang'] !== '' ? 'Tidak ada informasi yang cocok.' : 'Belum ada informasi ' . mb_strtolower($label) . '.'"
                                                 x-title="adaFilter ? 'Tidak ada informasi yang cocok.' : 'Belum ada informasi {{ mb_strtolower($label) }}.'" />
                        </td>
                    </tr>
                </x-admin.table>
            </div>
        </x-admin.card>

        <x-daftar.halaman :meta="$awal['meta']" />
    </div>
@endsection

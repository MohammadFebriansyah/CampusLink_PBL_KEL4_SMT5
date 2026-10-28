@extends('layouts.admin')

@section('title', 'Pengguna')

@section('content')
    @php
        $filter = $awal['filter'];
    @endphp

    <x-admin.page-header title="Pengguna" description="Cari akun, lihat detail, ubah identitas, atau nonaktifkan." />

    <x-daftar.data id="data-pengguna" :nilai="$awal" />

    <div x-data="daftar({ sumber: 'data-pengguna' })">
        <form @submit.prevent="muat(1)" role="search" class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <x-admin.icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <label for="q" class="sr-only">Nama atau email</label>
                <input id="q" type="search" maxlength="100" x-model="filter.q" value="{{ $filter['q'] }}" placeholder="Cari nama atau email" autocomplete="off"
                       class="h-10 w-full rounded-md border border-input bg-card pr-3 pl-9 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none">
            </div>
            <x-admin.select name="peran" label="Peran" placeholder="Semua peran" :selected="$filter['peran']" x-model="filter.peran"
                            :options="['mahasiswa' => 'Mahasiswa', 'dosen' => 'Dosen', 'mitra' => 'Mitra']" />
            <x-admin.select name="akun" label="Status akun" placeholder="Semua status" :selected="$filter['akun']" x-model="filter.akun"
                            :options="['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']" />
        </form>

        @if ($errors->any())
            <p class="mt-3 text-sm text-red-600 dark:text-red-400" role="alert">{{ $errors->first() }}</p>
        @endif
        <x-daftar.status />

        <x-admin.card flush class="mt-6 overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b border-border px-4 py-3">
                <p class="text-sm font-medium" aria-live="polite" x-text="`${meta.total} pengguna`">{{ $awal['meta']['total'] }} pengguna</p>
                <button type="button" x-cloak x-show="adaFilter" @click="aturUlang()" class="text-sm font-medium text-muted-foreground hover:text-foreground">Atur ulang</button>
            </div>
            <div class="transition-opacity" :class="memuat && 'opacity-60'" :aria-busy="memuat.toString()">
                <x-admin.table class="min-w-[44rem]">
                    <x-slot:head>
                        <x-daftar.th kunci="nama">Nama</x-daftar.th>
                        <x-daftar.th kunci="peran">Peran</x-daftar.th>
                        <x-daftar.th kunci="status">Status</x-daftar.th>
                        <th scope="col"><span class="sr-only">Aksi</span></th>
                    </x-slot:head>

                    <template x-for="u in items" :key="u.id">
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <x-admin.avatar x-text="u.inisial" />
                                    <div class="min-w-0">
                                        <p class="font-medium" x-text="u.nama"></p>
                                        <p class="truncate text-muted-foreground" x-text="u.email"></p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1.5">
                                    <x-admin.badge ::data-varian="u.peran.varian"><span x-text="u.peran.label"></span></x-admin.badge>
                                    <template x-if="u.google"><x-admin.badge variant="success">Google</x-admin.badge></template>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1.5">
                                    <x-admin.badge ::data-varian="u.aktif ? 'success' : 'danger'" dot><span x-text="u.aktif ? 'Aktif' : 'Nonaktif'"></span></x-admin.badge>
                                    <template x-if="u.verifikasi"><x-admin.badge variant="warning"><span x-text="u.verifikasi"></span></x-admin.badge></template>
                                    <template x-if="u.profil_disembunyikan"><x-admin.badge>Profil disembunyikan</x-admin.badge></template>
                                </div>
                            </td>
                            <td class="text-right">
                                <x-admin.button variant="outline" size="sm" href="#" ::href="u.url">Kelola</x-admin.button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="items.length === 0" @if (count($awal['data'])) x-cloak @endif>
                        <td colspan="4"><x-admin.empty-state title="Tidak ada pengguna yang cocok." icon="users" /></td>
                    </tr>
                </x-admin.table>
            </div>
        </x-admin.card>

        <x-daftar.halaman :meta="$awal['meta']" />
    </div>
@endsection

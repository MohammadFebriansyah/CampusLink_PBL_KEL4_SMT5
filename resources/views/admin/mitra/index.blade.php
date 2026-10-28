@extends('layouts.admin')

@section('title', 'Verifikasi mitra')

@section('content')
    @php
        $filter = $awal['filter'];
    @endphp

    <x-admin.page-header title="Verifikasi mitra" description="Mitra baru bisa masuk dan menayangkan informasi setelah disetujui." />

    <x-daftar.data id="data-mitra" :nilai="$awal" />

    <div x-data="daftar({ sumber: 'data-mitra', bukanFilter: ['verifikasi'], gulirKe: 'daftar-mitra' })" id="daftar-mitra" class="scroll-mt-20">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <x-admin.tabs label="Status verifikasi" model="verifikasi" :jumlah="$jumlah"
                          :items="['pending' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak']" />

            <form @submit.prevent="muat(1)" role="search" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="relative sm:w-72">
                    <x-admin.icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                    <label for="q" class="sr-only">Organisasi, penanggung jawab, atau email</label>
                    <input id="q" type="search" maxlength="100" x-model="filter.q" value="{{ $filter['q'] }}" placeholder="Cari organisasi atau email" autocomplete="off"
                           class="h-10 w-full rounded-md border border-input bg-card pr-3 pl-9 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none">
                </div>
                <x-daftar.urut :filter="$filter" :pilihan="['mendaftar:asc' => 'Mendaftar paling awal', 'mendaftar:desc' => 'Mendaftar terbaru', 'nama:asc' => 'Nama organisasi A-Z', 'nama:desc' => 'Nama organisasi Z-A']" />
            </form>
        </div>

        @if ($errors->any())
            <p class="mt-3 text-sm text-red-600 dark:text-red-400" role="alert">{{ $errors->first() }}</p>
        @endif
        <x-daftar.status />

        <div class="mt-6 grid gap-4 transition-opacity lg:grid-cols-2" :class="memuat && 'opacity-60'" :aria-busy="memuat.toString()">
            <template x-for="m in items" :key="m.id">
                <x-admin.card tag="article" class="flex flex-col [&>div]:flex [&>div]:flex-1 [&>div]:flex-col">
                    <div class="flex items-start gap-4">
                        <x-admin.avatar square class="size-11 text-sm" x-text="m.inisial" />
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <h2 class="font-semibold" x-text="m.nama_organisasi"></h2>
                                <x-admin.badge><span x-text="`Mendaftar ${m.mendaftar}`"></span></x-admin.badge>
                            </div>
                            <p class="mt-0.5 text-sm break-words text-muted-foreground" x-text="`${m.penanggung}, ${m.email}`"></p>
                        </div>
                    </div>

                    <dl class="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                        <div class="rounded-lg bg-muted px-3 py-2.5"><dt class="text-xs text-muted-foreground">Kontak</dt><dd class="font-medium break-words" x-text="m.kontak"></dd></div>
                        <div class="rounded-lg bg-muted px-3 py-2.5"><dt class="text-xs text-muted-foreground">Situs web</dt><dd class="font-medium break-words" x-text="m.website"></dd></div>
                        <template x-if="m.deskripsi">
                            <div class="rounded-lg bg-muted px-3 py-2.5 sm:col-span-2"><dt class="text-xs text-muted-foreground">Tentang organisasi</dt><dd x-text="m.deskripsi"></dd></div>
                        </template>
                    </dl>

                    <div class="mt-auto flex flex-wrap items-center gap-2 pt-5">
                        <template x-if="m.status !== 'disetujui'">
                            <x-admin.action x-url="m.url.setujui" variant="primary">Setujui</x-admin.action>
                        </template>
                        <template x-if="m.status !== 'ditolak'">
                            <x-admin.action x-url="m.url.tolak" confirm="Tolak mitra ini?" yes="Ya, tolak">Tolak</x-admin.action>
                        </template>
                        <x-admin.button variant="ghost" size="sm" class="ml-auto" href="#" ::href="m.url.akun">Lihat akun <x-admin.icon name="chevron-right" /></x-admin.button>
                    </div>
                </x-admin.card>
            </template>

            <x-admin.card class="lg:col-span-2" x-show="items.length === 0" :x-cloak="count($awal['data']) > 0">
                <x-admin.empty-state :title="$filter['q'] !== '' ? 'Tidak ada mitra yang cocok.' : ($filter['verifikasi'] === 'pending' ? 'Tidak ada mitra yang menunggu verifikasi.' : 'Belum ada mitra di daftar ini.')"
                                     x-title="adaFilter ? 'Tidak ada mitra yang cocok.' : (filter.verifikasi === 'pending' ? 'Tidak ada mitra yang menunggu verifikasi.' : 'Belum ada mitra di daftar ini.')" />
            </x-admin.card>
        </div>

        <x-daftar.halaman :meta="$awal['meta']" />
    </div>
@endsection

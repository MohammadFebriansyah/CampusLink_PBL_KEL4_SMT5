@extends('layouts.panel')

@section('title', 'Kelola ' . mb_strtolower($label))

@section('content')
    @php
        $filter = $awal['filter'];
        // Garis kiri kartu mengikuti status (data-varian).
        $garis = 'border-l-slate-300 data-[varian=success]:border-l-emerald-500 data-[varian=warning]:border-l-amber-500 dark:border-l-slate-600';
    @endphp

    <x-admin.page-header :title="'Informasi ' . mb_strtolower($label) . ' saya'"
                         :description="'Semua informasi ' . mb_strtolower($label) . ' yang Anda tayangkan, termasuk yang sudah ditutup.'">
        <x-slot:actions>
            <x-admin.button :href="route('mitra.informasi.create', $kategori)"><x-admin.icon name="plus" /> Tambah {{ mb_strtolower($label) }}</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-daftar.data id="data-informasi-mitra" :nilai="$awal" />

    <div x-data="daftar({ sumber: 'data-informasi-mitra' })">
        <form @submit.prevent="muat(1)" role="search" class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <x-admin.icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <label for="q" class="sr-only">Judul informasi</label>
                <input id="q" type="search" maxlength="100" x-model="filter.q" value="{{ $filter['q'] }}" placeholder="Cari judul" autocomplete="off"
                       class="h-10 w-full rounded-md border border-input bg-card pr-3 pl-9 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none">
            </div>
            <x-daftar.urut :filter="$filter" class="sm:w-56"
                           :pilihan="['dibuat:desc' => 'Terbaru ditambahkan', 'dibuat:asc' => 'Terlama ditambahkan', 'batas:asc' => 'Batas terdekat', 'batas:desc' => 'Batas terjauh', 'judul:asc' => 'Judul A-Z', 'pendaftar:desc' => 'Pendaftar terbanyak']" />
        </form>

        <x-daftar.status />

        <div class="mt-6 flex items-center justify-between gap-3">
            <p class="text-sm font-medium" aria-live="polite" x-text="`${meta.total} informasi`">{{ $awal['meta']['total'] }} informasi</p>
            <button type="button" x-cloak x-show="adaFilter" @click="aturUlang()" class="text-sm font-medium text-muted-foreground hover:text-foreground">Atur ulang</button>
        </div>

        <div class="mt-3 grid gap-4 transition-opacity lg:grid-cols-2" :class="memuat && 'opacity-60'" :aria-busy="memuat.toString()">
            <template x-for="item in items" :key="item.id">
                <article class="flex flex-col rounded-xl border border-l-4 border-border bg-card p-5 text-foreground shadow-sm {{ $garis }}" :data-varian="item.status.varian">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-semibold" x-text="item.judul"></h2>
                            <p class="mt-1 flex items-center gap-1.5 text-sm text-muted-foreground">
                                <x-admin.icon name="calendar" class="size-4" />
                                <span x-text="`Batas pendaftaran ${item.batas}`"></span>
                                <span class="font-medium text-accent-dark dark:text-accent" x-show="item.sisa" x-text="`· ${item.sisa}`"></span>
                            </p>
                        </div>
                        <x-admin.badge ::data-varian="item.status.varian" dot class="shrink-0"><span x-text="item.status.label"></span></x-admin.badge>
                    </div>

                    <a href="{{ route('mitra.pendaftar.index') }}" class="mt-3 inline-flex w-fit items-center gap-2 rounded-md bg-muted px-3 py-1.5 text-sm transition-colors hover:bg-sidebar-accent">
                        <x-admin.icon name="clipboard" class="size-4 text-muted-foreground" />
                        <span class="font-medium" x-text="item.pendaftar"></span>
                        <span class="rounded-full bg-accent px-1.5 text-xs font-semibold text-navy tabular-nums" x-show="item.menunggu" x-text="item.menunggu"></span>
                    </a>

                    <p class="mt-3 rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-900 dark:bg-amber-500/10 dark:text-amber-200" x-show="item.status.kunci === 'disembunyikan'">Informasi ini tidak tampil untuk pembaca. Hubungi admin CampusLink bila menurut Anda ini keliru.</p>

                    <div class="mt-auto flex flex-wrap items-center gap-1.5 pt-4">
                        <template x-if="item.url.lihat">
                            <x-admin.button variant="outline" size="sm" href="#" ::href="item.url.lihat">Lihat</x-admin.button>
                        </template>
                        <x-admin.button variant="outline" size="sm" href="#" ::href="item.url.ubah">Ubah</x-admin.button>
                        <div class="ml-auto">
                            @include('mitra.informasi._hapus')
                        </div>
                    </div>
                </article>
            </template>

            <div class="rounded-xl border border-dashed border-border bg-card text-foreground lg:col-span-2" x-show="items.length === 0" @if (count($awal['data'])) x-cloak @endif>
                <div x-show="adaFilter" @if ($filter['q'] === '') x-cloak @endif>
                    <x-admin.empty-state title="Tidak ada informasi yang cocok." icon="search" class="py-8">Coba kata kunci lain.</x-admin.empty-state>
                </div>
                <div x-show="! adaFilter" @if ($filter['q'] !== '') x-cloak @endif>
                    <x-admin.empty-state :title="'Belum ada informasi ' . mb_strtolower($label) . '.'" :icon="$kategori" class="py-8">
                        Informasi yang Anda tambahkan langsung tayang untuk mahasiswa dan dosen.
                    </x-admin.empty-state>
                    <div class="-mt-4 flex justify-center pb-4">
                        <x-admin.button :href="route('mitra.informasi.create', $kategori)"><x-admin.icon name="plus" /> Tambah {{ mb_strtolower($label) }} pertama</x-admin.button>
                    </div>
                </div>
            </div>
        </div>

        <x-daftar.halaman :meta="$awal['meta']" />
    </div>
@endsection

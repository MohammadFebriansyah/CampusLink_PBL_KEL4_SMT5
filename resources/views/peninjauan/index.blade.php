{{-- Dipakai panel admin (layouts.admin) dan panel mitra (layouts.panel); isi memakai komponen x-admin.* untuk keduanya. --}}
@extends($panel === 'admin' ? 'layouts.admin' : 'layouts.panel')

@section('title', 'Pendaftar')

@section('content')
    @php
        $filter = $awal['filter'];
        $judul = 'Pendaftar';
        $deskripsi = $panel === 'admin'
            ? 'Mahasiswa yang mendaftar ke kegiatan kampus buatan admin. Tinjau profil dan CV, lalu terima atau tolak.'
            : 'Mahasiswa yang mendaftar ke informasi Anda. Tinjau profil dan CV, lalu terima atau tolak.';
        $kosong = ['menunggu' => 'Tidak ada pendaftar yang menunggu ditinjau.', 'diterima' => 'Belum ada pendaftar diterima.', 'ditolak' => 'Belum ada pendaftar ditolak.'];
    @endphp

    @if ($panel === 'admin')
        <x-admin.page-header :title="$judul" :description="$deskripsi" />
    @else
        @include('mitra._kepala', ['judul' => $judul, 'deskripsi' => $deskripsi])
    @endif

    <x-daftar.data id="data-pendaftar" :nilai="$awal" />

    <div @class(['mx-auto max-w-page px-4 py-8 sm:px-6 md:py-10' => $panel !== 'admin'])
         x-data="daftar({ sumber: 'data-pendaftar', bukanFilter: ['status'] })">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <x-admin.tabs label="Status pendaftar" model="status" :jumlah="$jumlah"
                          :items="['menunggu' => 'Menunggu', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak']" />

            <form @submit.prevent="muat(1)" role="search" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="relative sm:w-72">
                    <x-admin.icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                    <label for="q" class="sr-only">Nama atau email pendaftar</label>
                    <input id="q" type="search" maxlength="100" x-model="filter.q" value="{{ $filter['q'] }}" placeholder="Cari nama atau email" autocomplete="off"
                           class="h-10 w-full rounded-md border border-input bg-card pr-3 pl-9 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none">
                </div>
                <x-daftar.urut :filter="$filter" :pilihan="['mendaftar:desc' => 'Mendaftar terbaru', 'mendaftar:asc' => 'Mendaftar paling awal']" />
            </form>
        </div>

        <x-daftar.status />

        <div class="mt-6 grid gap-4 transition-opacity lg:grid-cols-2" :class="memuat && 'opacity-60'" :aria-busy="memuat.toString()">
            <template x-for="p in items" :key="p.id">
                <a :href="p.url"
                   class="group flex items-start gap-4 rounded-xl border border-border bg-card p-5 text-foreground shadow-sm transition hover:border-foreground/20 hover:shadow-md">
                    <x-daftar.foto data="p" ukuran="size-11" />
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <p class="font-semibold group-hover:underline" x-text="p.nama"></p>
                            <x-admin.badge ::data-varian="p.status.varian" dot><span x-text="p.status.label"></span></x-admin.badge>
                        </div>
                        <p class="text-sm text-muted-foreground" x-show="p.keterangan" x-text="p.keterangan"></p>
                        <p class="mt-2 text-sm">
                            <span class="font-semibold text-accent-dark dark:text-accent" x-text="p.kategori"></span>
                            <span x-text="p.judul"></span>
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground" x-text="`Mendaftar ${p.mendaftar}`"></p>
                    </div>
                </a>
            </template>

            <x-admin.card class="lg:col-span-2" x-show="items.length === 0" :x-cloak="count($awal['data']) > 0">
                <x-admin.empty-state icon="clipboard"
                                     :title="$filter['q'] !== '' ? 'Tidak ada pendaftar yang cocok.' : $kosong[$filter['status']]"
                                     x-title="adaFilter ? 'Tidak ada pendaftar yang cocok.' : {{ \Illuminate\Support\Js::from($kosong) }}[filter.status]">
                    Pendaftar muncul di sini saat mahasiswa mendaftar dengan CV ke informasi yang didaftar lewat CampusLink.
                </x-admin.empty-state>
            </x-admin.card>
        </div>

        <x-daftar.halaman :meta="$awal['meta']" />
    </div>
@endsection

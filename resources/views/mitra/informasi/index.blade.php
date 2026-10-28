@extends('layouts.panel')

@section('title', 'Kelola ' . mb_strtolower($label))

@section('content')
    @php
        $filter = $awal['filter'];
        // Garis kiri kartu dan titik status mengikuti status (data-varian).
        $garis = 'border-l-[#94a3b8] data-[varian=success]:border-l-[#16a34a] data-[varian=warning]:border-l-accent-dark';
    @endphp

    @include('mitra._kepala', [
        'judul' => 'Informasi ' . mb_strtolower($label) . ' saya',
        'deskripsi' => 'Semua informasi ' . mb_strtolower($label) . ' yang Anda tayangkan, termasuk yang sudah ditutup.',
        'aksi' => '<a href="' . e(route('mitra.informasi.create', $kategori)) . '" class="btn btn-primary px-6 py-3">+ Tambah ' . e(mb_strtolower($label)) . '</a>',
    ])

    <x-daftar.data id="data-informasi-mitra" :nilai="$awal" />

    <section class="bg-[#f7f8fc]">
        <div class="mx-auto max-w-page px-4 py-8 sm:px-6 md:py-10" x-data="daftar({ sumber: 'data-informasi-mitra' })">
            <form @submit.prevent="muat(1)" role="search" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="flex flex-1 items-center gap-2 rounded-full bg-paper-white px-4 ring-1 ring-fog">
                    <svg class="size-5 shrink-0 text-ash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <label for="q" class="sr-only">Judul informasi</label>
                    <input id="q" type="search" maxlength="100" x-model="filter.q" value="{{ $filter['q'] }}" placeholder="Cari judul" autocomplete="off"
                           class="w-full bg-transparent py-2.5 placeholder:text-ash focus:outline-none">
                </div>
                <x-daftar.urut :filter="$filter" class="rounded-full! border-fog! bg-paper-white! px-4 sm:w-56"
                               :pilihan="['dibuat:desc' => 'Terbaru ditambahkan', 'dibuat:asc' => 'Terlama ditambahkan', 'batas:asc' => 'Batas terdekat', 'batas:desc' => 'Batas terjauh', 'judul:asc' => 'Judul A-Z', 'pendaftar:desc' => 'Pendaftar terbanyak']" />
            </form>

            <x-daftar.status />

            <p class="mt-6 text-sm font-semibold text-carbon" aria-live="polite" x-text="`${meta.total} informasi`">{{ $awal['meta']['total'] }} informasi</p>

            <div class="mt-3 grid gap-3 transition-opacity lg:grid-cols-2" :class="memuat && 'opacity-60'" :aria-busy="memuat.toString()">
                <template x-for="item in items" :key="item.id">
                    <article class="flex flex-col rounded-2xl border border-l-4 border-fog bg-paper-white p-5 {{ $garis }}" :data-varian="item.status.varian">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="font-semibold text-carbon" x-text="item.judul"></h2>
                                <p class="mt-1 flex items-center gap-1.5 text-sm text-graphite">
                                    <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                                    <span x-text="`Batas pendaftaran ${item.batas}`"></span>
                                    <span class="text-accent-dark" x-show="item.sisa" x-text="`· ${item.sisa}`"></span>
                                </p>
                            </div>
                            <x-admin.badge ::data-varian="item.status.varian" dot class="shrink-0"><span x-text="item.status.label"></span></x-admin.badge>
                        </div>

                        <a href="{{ route('mitra.pendaftar.index') }}" class="mt-3 inline-flex w-fit items-center gap-2 rounded-xl bg-[#f7f8fc] px-3 py-2 text-sm hover:bg-primary-wash">
                            <span class="font-semibold text-carbon" x-text="item.pendaftar"></span>
                            <span class="rounded-full bg-accent px-2 py-0.5 text-[11px] font-bold text-navy" x-show="item.menunggu" x-text="item.menunggu"></span>
                        </a>

                        <p class="mt-3 rounded-xl bg-accent-wash px-3 py-2 text-sm text-carbon" x-show="item.status.kunci === 'disembunyikan'">Informasi ini tidak tampil untuk pembaca. Hubungi admin CampusLink bila menurut Anda ini keliru.</p>

                        <div class="mt-auto flex flex-wrap items-center gap-2 pt-4">
                            <template x-if="item.url.lihat">
                                <a :href="item.url.lihat" class="inline-flex items-center rounded-full border border-fog px-3.5 py-1.5 text-sm font-medium transition hover:border-accent/50 hover:text-accent-dark">Lihat</a>
                            </template>
                            <a :href="item.url.ubah" class="inline-flex items-center rounded-full border border-fog px-3.5 py-1.5 text-sm font-medium transition hover:border-accent/50 hover:text-accent-dark">Ubah</a>
                            <div class="ml-auto">
                                @include('mitra.informasi._hapus')
                            </div>
                        </div>
                    </article>
                </template>

                <div class="rounded-3xl border border-dashed border-fog bg-paper-white px-6 py-14 text-center lg:col-span-2" x-show="items.length === 0" @if (count($awal['data'])) x-cloak @endif>
                    <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-accent-wash text-2xl font-bold text-accent-dark">+</span>
                    <div x-show="adaFilter" @if ($filter['q'] === '') x-cloak @endif>
                        <p class="mt-4 text-subheading font-semibold">Tidak ada informasi yang cocok.</p>
                        <p class="mt-1 text-graphite">Coba kata kunci lain.</p>
                    </div>
                    <div x-show="! adaFilter" @if ($filter['q'] !== '') x-cloak @endif>
                        <p class="mt-4 text-subheading font-semibold">Belum ada informasi {{ mb_strtolower($label) }}.</p>
                        <p class="mt-1 text-graphite">Informasi yang Anda tambahkan langsung tayang untuk mahasiswa dan dosen.</p>
                        <a href="{{ route('mitra.informasi.create', $kategori) }}" class="btn btn-primary mt-5 px-6 py-3">Tambah {{ mb_strtolower($label) }} pertama</a>
                    </div>
                </div>
            </div>

            <x-daftar.halaman :meta="$awal['meta']" />
        </div>
    </section>
@endsection

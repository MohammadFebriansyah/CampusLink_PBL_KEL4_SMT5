@extends('layouts.panel')

@section('title', 'Informasi ' . mb_strtolower($label))

@php
    $deskripsiKategori = [
        'magang' => 'Pengalaman kerja nyata dari mitra yang sudah diverifikasi admin.',
        'lomba' => 'Kompetisi tingkat kampus sampai internasional untuk mengasah prestasi.',
        'beasiswa' => 'Bantuan biaya kuliah dari lembaga dan perusahaan mitra.',
    ];
    $filter = $awal['filter'];
    $adaFilter = $kata !== '' || $tingkat;
@endphp

@section('content')
    <x-admin.page-header :title="'Informasi ' . mb_strtolower($label)" :description="$deskripsiKategori[$kategori] . ' Hanya yang masih dibuka.'" />

    <x-daftar.data id="data-informasi" :nilai="$awal" />

    <div x-data="daftar({ sumber: 'data-informasi', gulirKe: 'hasil' })">
        <form @submit.prevent="muat(1)" role="search" class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <x-admin.icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <label for="q" class="sr-only">Kata kunci</label>
                <input id="q" type="search" x-model="filter.q" value="{{ $kata }}" maxlength="100" autocomplete="off"
                       placeholder="Cari judul, penyelenggara, atau persyaratan"
                       :aria-invalid="galat ? 'true' : null" @error('q') aria-invalid="true" @enderror
                       class="h-10 w-full rounded-md border border-input bg-card pr-9 pl-9 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none">
                <span x-cloak x-show="memuat" class="absolute top-1/2 right-3 size-4 -translate-y-1/2 animate-spin rounded-full border-2 border-accent/30 border-t-accent" role="status" aria-label="Mencari"></span>
            </div>

            @if ($kategori === 'lomba')
                <x-admin.select name="tingkat" label="Tingkat" placeholder="Semua tingkat" :selected="$tingkat" x-model="filter.tingkat" class="sm:w-44"
                                :options="collect($daftarTingkat)->mapWithKeys(fn ($t) => [$t => ucfirst($t)])->all()" />
            @endif

            <x-daftar.urut :filter="$filter" class="sm:w-48"
                           :pilihan="['batas:asc' => 'Batas terdekat', 'terbaru:desc' => 'Terbaru ditambahkan', 'judul:asc' => 'Judul A-Z']" />
        </form>

        @error('q')
            <p class="mt-3 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
        @enderror
        @error('tingkat')
            <p class="mt-3 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
        @enderror
        <p x-cloak x-show="galat" class="mt-3 text-sm text-red-600 dark:text-red-400" role="alert" x-text="galat"></p>

        <x-daftar.status />

        {{-- Daftar hasil (kiri) + panel detail (kanan, hanya layar lebar) --}}
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,0.75fr)_minmax(0,1.25fr)] lg:items-start"
             x-data="panelDetail({{ $detail ? $detail['item']->id : 'null' }})"
             @daftar-dimuat="setelahDimuat($event.detail.items)">
            <div class="min-w-0 scroll-mt-24" id="hasil">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-muted-foreground" aria-live="polite">
                        <span class="font-medium text-foreground" x-text="`${meta.total} informasi dibuka`">{{ $awal['meta']['total'] }} informasi dibuka</span>
                        <span x-show="filter.q" @if ($kata === '') x-cloak @endif x-text="`untuk “${filter.q}”`">untuk &ldquo;{{ $kata }}&rdquo;</span>
                    </p>
                    <button type="button" x-show="adaFilter" @if (! $adaFilter) x-cloak @endif @click="aturUlang()"
                            class="text-sm font-medium text-muted-foreground hover:text-foreground">Hapus pencarian</button>
                </div>

                {{-- Saat mencari, kata kunci bisa dibawa ke kategori lain --}}
                <p class="mt-2 text-sm text-muted-foreground" x-show="filter.q" @if ($kata === '') x-cloak @endif>
                    Cari juga di
                    @foreach (collect($daftarKategori)->except($kategori) as $kunci => $nama)
                        <a href="{{ route($kunci . '.index', ['q' => $kata]) }}" :href="'{{ route($kunci . '.index') }}?q=' + encodeURIComponent(filter.q)"
                           class="font-medium text-foreground underline-offset-2 hover:underline">{{ $nama }}</a>@if (! $loop->last) · @endif
                    @endforeach
                </p>

                <div class="mt-4 space-y-2.5 transition-opacity" :class="memuat && 'opacity-60'" :aria-busy="memuat.toString()">
                    <template x-for="item in items" :key="item.id">
                        @include('informasi._baris')
                    </template>

                    <div class="rounded-xl border border-dashed border-border bg-card text-foreground" x-show="items.length === 0" @if (count($awal['data'])) x-cloak @endif>
                        <div x-show="adaFilter" @if (! $adaFilter) x-cloak @endif>
                            <x-admin.empty-state title="Tidak ada hasil yang cocok." icon="search">Coba kata kunci lain, atau hapus pencarian untuk melihat semua.</x-admin.empty-state>
                        </div>
                        <div x-show="! adaFilter" @if ($adaFilter) x-cloak @endif>
                            <x-admin.empty-state :title="'Belum ada informasi ' . mb_strtolower($label) . ' yang dibuka.'" :icon="$kategori">Coba lagi nanti, atau lihat kategori lain lewat menu Jelajahi.</x-admin.empty-state>
                        </div>
                    </div>
                </div>

                <x-daftar.halaman :meta="$awal['meta']" />
            </div>

            {{-- Panel detail: menempel saat daftar di-scroll, isinya diganti tanpa pindah halaman --}}
            <aside class="hidden lg:sticky lg:top-20 lg:block" aria-label="Detail informasi" aria-live="polite" x-show="items.length > 0" @if (! $detail) x-cloak @endif>
                <div x-ref="panel" class="relative max-h-[calc(100svh-6.5rem)] overflow-y-auto overscroll-contain rounded-xl border border-border bg-card p-6 text-foreground shadow-sm">
                    <div x-show="memuatDetail" x-cloak x-transition.opacity class="absolute inset-0 z-10 flex items-start justify-center bg-card/70 pt-24 backdrop-blur-[1px]">
                        <span class="size-8 animate-spin rounded-full border-[3px] border-accent/30 border-t-accent" role="status" aria-label="Memuat"></span>
                    </div>
                    <div x-ref="isi">
                        @if ($detail)
                            @include('informasi._detail', $detail + ['panel' => true])
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Informasi ' . mb_strtolower($label))

@php
    $ikonKategori = [
        'magang' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 13h18"/>',
        'lomba' => '<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4Z"/><path d="M17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/>',
        'beasiswa' => '<path d="m12 4 10 5-10 5L2 9l10-5Z"/><path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5M22 9v6"/>',
    ];
    $deskripsiKategori = [
        'magang' => 'Pengalaman kerja nyata dari mitra yang sudah diverifikasi admin.',
        'lomba' => 'Kompetisi tingkat kampus sampai internasional untuk mengasah prestasi.',
        'beasiswa' => 'Bantuan biaya kuliah dari lembaga dan perusahaan mitra.',
    ];
    $filter = $awal['filter'];
    $adaFilter = $kata !== '' || $tingkat;
@endphp

@section('content')
    <x-daftar.data id="data-informasi" :nilai="$awal" />

    <div x-data="daftar({ sumber: 'data-informasi', gulirKe: 'hasil' })">
        {{-- Kepala halaman: pita navy berisi judul dan kolom cari --}}
        <section class="relative isolate overflow-hidden bg-navy text-paper-white">
            <div class="hero-dots pointer-events-none absolute inset-0" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -top-32 right-[-5%] size-[420px] rounded-full bg-primary/50 blur-3xl" aria-hidden="true"></div>

            <div class="relative mx-auto max-w-page px-4 pt-10 pb-12 sm:px-6 md:pt-14 md:pb-16">
                <nav class="flex items-center gap-2 text-sm text-white/60" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="hover:text-paper-white">Beranda</a>
                    <span aria-hidden="true">/</span>
                    <span class="text-paper-white">{{ $label }}</span>
                </nav>

                <div class="mt-6 flex items-start gap-4">
                    <span class="hidden size-14 shrink-0 items-center justify-center rounded-2xl bg-accent text-navy sm:flex">
                        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $ikonKategori[$kategori] !!}</svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-heading font-bold tracking-[-0.8px] sm:text-heading-lg">Informasi {{ mb_strtolower($label) }}</h1>
                        <p class="mt-2 text-white/70">{{ $deskripsiKategori[$kategori] }} Hanya yang masih dibuka.</p>
                    </div>
                </div>

                <form @submit.prevent="muat(1)" role="search"
                      class="mt-8 flex flex-col gap-2 rounded-3xl bg-paper-white p-2 shadow-[0_20px_50px_-20px_rgb(0_0_0/0.5)] sm:flex-row sm:items-center sm:rounded-full">
                    <div class="flex flex-1 items-center gap-2 px-3">
                        <svg class="size-5 shrink-0 text-ash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                        </svg>
                        <label for="q" class="sr-only">Kata kunci</label>
                        <input id="q" type="search" x-model="filter.q" value="{{ $kata }}" maxlength="100" autocomplete="off"
                               placeholder="Cari judul, penyelenggara, atau persyaratan"
                               :aria-invalid="galat ? 'true' : null" @error('q') aria-invalid="true" @enderror
                               class="w-full bg-transparent py-3 text-body text-carbon placeholder:text-ash focus:outline-none">
                        <span x-cloak x-show="memuat" class="size-4 shrink-0 animate-spin rounded-full border-2 border-accent/30 border-t-accent" role="status" aria-label="Mencari"></span>
                    </div>

                    @if ($kategori === 'lomba')
                        <div class="sm:border-l sm:border-fog sm:pl-2">
                            <label for="tingkat" class="sr-only">Tingkat</label>
                            <select id="tingkat" x-model="filter.tingkat" x-combobox class="w-full rounded-full bg-mist px-4 py-3 text-sm font-medium text-carbon sm:w-44 sm:py-2.5">
                                <option value="">Semua tingkat</option>
                                @foreach ($daftarTingkat as $nilai)
                                    <option value="{{ $nilai }}" @selected($tingkat === $nilai)>{{ ucfirst($nilai) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="sm:border-l sm:border-fog sm:pl-2">
                        <x-daftar.urut :filter="$filter" class="w-full rounded-full! border-0! bg-mist! px-4 py-3 text-carbon! sm:w-48 sm:py-2.5"
                                       :pilihan="['batas:asc' => 'Batas terdekat', 'terbaru:desc' => 'Terbaru ditambahkan', 'judul:asc' => 'Judul A-Z']" />
                    </div>
                </form>

                @error('q')
                    <p class="mt-3 text-sm text-[#fdba74]">{{ $message }}</p>
                @enderror
                @error('tingkat')
                    <p class="mt-3 text-sm text-[#fdba74]">{{ $message }}</p>
                @enderror
                <p x-cloak x-show="galat" class="mt-3 text-sm text-[#fdba74]" role="alert" x-text="galat"></p>
            </div>
        </section>

        {{-- Daftar hasil (kiri) + panel detail (kanan, hanya layar lebar) --}}
        <section class="bg-[#f7f8fc]">
            <div class="mx-auto grid max-w-page grid-cols-1 gap-6 px-4 py-10 sm:px-6 md:py-12 lg:grid-cols-[minmax(0,0.75fr)_minmax(0,1.25fr)] lg:items-start"
                 x-data="panelDetail({{ $detail ? $detail['item']->id : 'null' }})"
                 @daftar-dimuat="setelahDimuat($event.detail.items)">
                <div class="min-w-0 scroll-mt-24" id="hasil">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm text-graphite" aria-live="polite">
                            <span class="font-semibold text-carbon" x-text="`${meta.total} informasi dibuka`">{{ $awal['meta']['total'] }} informasi dibuka</span>
                            <span x-show="filter.q" @if ($kata === '') x-cloak @endif x-text="`untuk “${filter.q}”`">untuk &ldquo;{{ $kata }}&rdquo;</span>
                        </p>
                        <button type="button" x-show="adaFilter" @if (! $adaFilter) x-cloak @endif @click="aturUlang()"
                                class="text-sm font-medium text-accent-dark hover:underline">Hapus pencarian</button>
                    </div>

                    {{-- Saat mencari, kata kunci bisa dibawa ke kategori lain --}}
                    <p class="mt-2 text-sm text-graphite" x-show="filter.q" @if ($kata === '') x-cloak @endif>
                        Cari juga di
                        @foreach (collect($daftarKategori)->except($kategori) as $kunci => $nama)
                            <a href="{{ route($kunci . '.index', ['q' => $kata]) }}" :href="'{{ route($kunci . '.index') }}?q=' + encodeURIComponent(filter.q)"
                               class="font-medium text-primary hover:underline">{{ $nama }}</a>@if (! $loop->last) · @endif
                        @endforeach
                    </p>

                    <div class="mt-5 space-y-3 transition-opacity lg:space-y-2.5" :class="memuat && 'opacity-60'" :aria-busy="memuat.toString()">
                        <template x-for="item in items" :key="item.id">
                            @include('informasi._baris')
                        </template>

                        <div class="rounded-3xl border border-dashed border-fog bg-paper-white px-6 py-14 text-center" x-show="items.length === 0" @if (count($awal['data'])) x-cloak @endif>
                            <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-accent-wash text-accent-dark">
                                <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $ikonKategori[$kategori] !!}</svg>
                            </span>
                            <div x-show="adaFilter" @if (! $adaFilter) x-cloak @endif>
                                <p class="mt-4 text-subheading font-semibold">Tidak ada hasil yang cocok.</p>
                                <p class="mt-1 text-graphite">Coba kata kunci lain, atau hapus pencarian untuk melihat semua.</p>
                            </div>
                            <div x-show="! adaFilter" @if ($adaFilter) x-cloak @endif>
                                <p class="mt-4 text-subheading font-semibold">Belum ada informasi {{ mb_strtolower($label) }} yang dibuka.</p>
                                <p class="mt-1 text-graphite">Coba lagi nanti, atau lihat kategori lain lewat menu di atas.</p>
                            </div>
                        </div>
                    </div>

                    <x-daftar.halaman :meta="$awal['meta']" />
                </div>

                {{-- Panel detail: menempel saat daftar di-scroll, isinya diganti tanpa pindah halaman --}}
                <aside class="hidden lg:sticky lg:top-24 lg:block" aria-label="Detail informasi" aria-live="polite" x-show="items.length > 0" @if (! $detail) x-cloak @endif>
                    <div x-ref="panel" class="relative max-h-[calc(100svh-7rem)] overflow-y-auto overscroll-contain rounded-3xl border border-fog bg-paper-white p-7 shadow-[0_24px_60px_-36px_rgb(15_30_61/0.5)]">
                        <div x-show="memuatDetail" x-cloak x-transition.opacity class="absolute inset-0 z-10 flex items-start justify-center bg-paper-white/70 pt-24 backdrop-blur-[1px]">
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
        </section>
    </div>
@endsection

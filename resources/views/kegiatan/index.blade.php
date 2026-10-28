@extends('layouts.panel')

@section('title', 'Kegiatan saya')

@section('content')
    @php
        $filter = $awal['filter'];
        $titik = ['menunggu' => 'bg-amber-500', 'diterima' => 'bg-emerald-500', 'ditolak' => 'bg-red-500'];
        $kunciAwal = $detail ? $detail['kategori'] . '-' . $detail['item']->id : null;
        $adaFilter = $filter['q'] !== '' || $filter['status'] !== '';
    @endphp

    <x-daftar.data id="data-kegiatan" :nilai="$awal" />

    {{-- Daftar (kiri) + panel detail (kanan, layar lebar). Di HP, kartu membuka halaman detail. --}}
    <div x-data="daftar({ sumber: 'data-kegiatan', bukanFilter: ['bagian'], gulirKe: 'hasil' })">
        <x-admin.page-header title="Kegiatan saya"
                             description="Status pendaftaran magang, lomba, dan beasiswa, serta permintaan bimbingan ke dosen. Klik kegiatan untuk melihat detailnya.">
            <x-slot:actions>
                <div class="flex flex-wrap gap-1.5" aria-label="Jumlah pendaftaran per status">
                    @foreach (\App\Support\StatusPeninjauan::LABEL as $s => $nama)
                        <x-admin.badge>
                            <span class="size-1.5 rounded-full {{ $titik[$s] }}" aria-hidden="true"></span>
                            {{ $nama }} <span class="font-semibold text-foreground tabular-nums" x-text="tambahan.jumlah_status?.['{{ $s }}'] ?? 0">{{ $jumlahStatus[$s] ?? 0 }}</span>
                        </x-admin.badge>
                    @endforeach
                </div>
            </x-slot:actions>
        </x-admin.page-header>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:items-start"
             x-data="panelDetail(@js($kunciAwal))"
             @daftar-dimuat="setelahDimuat($event.detail.items, (i) => i.kunci)">
            <div class="min-w-0 scroll-mt-24" id="hasil">
                {{-- Pendaftaran atau bimbingan --}}
                <div class="inline-flex max-w-full flex-wrap gap-1 rounded-lg bg-muted p-1" role="group" aria-label="Jenis kegiatan">
                    @foreach (['pendaftaran' => 'Pendaftaran', 'bimbingan' => 'Bimbingan dosen'] as $kunci => $nama)
                        <button type="button" @click="filter.bagian = '{{ $kunci }}'"
                                :aria-pressed="(filter.bagian === '{{ $kunci }}').toString()" aria-pressed="{{ $filter['bagian'] === $kunci ? 'true' : 'false' }}"
                                class="group/tab inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground aria-pressed:bg-card aria-pressed:text-foreground aria-pressed:shadow-sm">
                            {{ $nama }}
                            <span class="rounded-full bg-background px-1.5 text-xs text-muted-foreground tabular-nums group-aria-pressed/tab:bg-accent group-aria-pressed/tab:text-navy"
                                  x-text="tambahan.jumlah_bagian?.['{{ $kunci }}'] ?? 0">{{ $awal['tambahan']['jumlah_bagian'][$kunci] ?? 0 }}</span>
                        </button>
                    @endforeach
                </div>

                <form @submit.prevent="muat(1)" role="search" class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <x-admin.icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <label for="q" class="sr-only">Judul kegiatan atau nama dosen</label>
                        <input id="q" type="search" maxlength="100" x-model="filter.q" value="{{ $filter['q'] }}" autocomplete="off"
                               :placeholder="filter.bagian === 'bimbingan' ? 'Cari judul kegiatan atau dosen' : 'Cari judul kegiatan'" placeholder="Cari judul kegiatan"
                               class="h-10 w-full rounded-md border border-input bg-card pr-3 pl-9 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none">
                    </div>
                    <x-admin.select name="status" label="Status" placeholder="Semua status" :selected="$filter['status']" x-model="filter.status"
                                    :options="\App\Support\StatusPeninjauan::LABEL" class="sm:w-40" />
                    <x-daftar.urut :filter="$filter" class="sm:w-36" :pilihan="['dikirim:desc' => 'Terbaru', 'dikirim:asc' => 'Terlama']" />
                </form>

                <x-daftar.status />

                <div class="mt-4 space-y-2.5 transition-opacity" :class="memuat && 'opacity-60'" :aria-busy="memuat.toString()">
                    <template x-for="k in items" :key="filter.bagian + k.id">
                        <a :href="k.url"
                           @click="k.url && buka($event, k.kunci)"
                           :class="{ 'pointer-events-none opacity-70': ! k.url, 'lg:border-foreground/30 lg:ring-2 lg:ring-accent/40': k.url && aktif === k.kunci }"
                           :aria-current="k.url && aktif === k.kunci ? 'true' : null"
                           class="group block rounded-xl border border-border bg-card p-4 text-foreground shadow-sm transition hover:border-foreground/20 hover:shadow-md">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    {{-- Pendaftaran: kategori + judul + penyelenggara. Bimbingan: dosen + kategori: judul. --}}
                                    <template x-if="k.dosen === undefined">
                                        <div>
                                            <p class="text-xs font-medium text-accent-dark dark:text-accent" x-text="k.kategori"></p>
                                            <p class="mt-0.5 font-semibold group-hover:underline" x-text="k.judul"></p>
                                            <p class="truncate text-xs text-muted-foreground" x-show="k.penyelenggara" x-text="k.penyelenggara"></p>
                                        </div>
                                    </template>
                                    <template x-if="k.dosen !== undefined">
                                        <div>
                                            <p class="font-semibold group-hover:underline" x-text="k.dosen"></p>
                                            <p class="truncate text-xs text-muted-foreground" x-text="`${k.kategori}: ${k.judul}`"></p>
                                        </div>
                                    </template>
                                </div>
                                <x-admin.badge ::data-varian="k.status.varian" dot class="shrink-0"><span x-text="k.status.label"></span></x-admin.badge>
                            </div>
                            <p class="mt-2 text-xs text-muted-foreground" x-text="k.waktu"></p>
                            <p x-show="k.catatan" class="mt-2 line-clamp-2 rounded-md bg-muted px-2.5 py-1.5 text-sm">
                                <span class="font-medium" x-text="k.dosen === undefined ? 'Catatan:' : 'Catatan dosen:'"></span> <span x-text="k.catatan"></span>
                            </p>
                        </a>
                    </template>

                    <div class="rounded-xl border border-dashed border-border bg-card px-6 py-10 text-center text-foreground" x-show="items.length === 0" @if (count($awal['data'])) x-cloak @endif>
                        <div x-show="adaFilter" @if (! $adaFilter) x-cloak @endif>
                            <p class="font-semibold">Tidak ada kegiatan yang cocok.</p>
                            <button type="button" @click="aturUlang()" class="mt-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:underline">Hapus pencarian</button>
                        </div>
                        <div x-show="! adaFilter && filter.bagian === 'pendaftaran'" @if ($adaFilter || $filter['bagian'] !== 'pendaftaran') x-cloak @endif>
                            <p class="font-semibold">Belum ada pendaftaran.</p>
                            <p class="mt-1 text-sm text-muted-foreground">Cari kegiatan yang bertanda "Daftar di CampusLink", lalu unggah CV Anda.</p>
                            <div class="mt-4 flex flex-wrap justify-center gap-2">
                                <x-admin.button :href="route('magang.index')">Lihat magang</x-admin.button>
                                <x-admin.button variant="outline" :href="route('lomba.index')">Lomba</x-admin.button>
                                <x-admin.button variant="outline" :href="route('beasiswa.index')">Beasiswa</x-admin.button>
                            </div>
                        </div>
                        <div x-show="! adaFilter && filter.bagian === 'bimbingan'" @if ($adaFilter || $filter['bagian'] !== 'bimbingan') x-cloak @endif>
                            <p class="font-semibold">Belum ada permintaan bimbingan.</p>
                            <p class="mt-1 text-sm text-muted-foreground">Setelah Anda diterima di magang atau lomba, pilih dosen pembimbing di detail kegiatannya.</p>
                        </div>
                    </div>
                </div>

                <x-daftar.halaman :meta="$awal['meta']" />
            </div>

            {{-- Panel detail: status, CV, batal, dan dosen pembimbing tampil di sini tanpa pindah halaman --}}
            <aside class="hidden lg:sticky lg:top-20 lg:block" aria-label="Detail kegiatan" aria-live="polite" x-show="items.some((i) => i.url)" @if (! $detail) x-cloak @endif>
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

@extends('layouts.app')

@section('title', 'Kegiatan saya')

@section('content')
    @php
        $filter = $awal['filter'];
        $titik = ['menunggu' => 'bg-accent-dark', 'diterima' => 'bg-[#16a34a]', 'ditolak' => 'bg-[#dc2626]'];
        $kunciAwal = $detail ? $detail['kategori'] . '-' . $detail['item']->id : null;
        $adaFilter = $filter['q'] !== '' || $filter['status'] !== '';
        // Lencana status: warna mengikuti data-varian (warning/success/danger), selalu disertai label.
        $lencana = 'inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold
            data-[varian=warning]:bg-accent-wash data-[varian=warning]:text-accent-dark
            data-[varian=success]:bg-mint-wash data-[varian=success]:text-[#15803d]
            data-[varian=danger]:bg-[#fee2e2] data-[varian=danger]:text-[#b91c1c]';
        $kartu = 'group block rounded-2xl border border-fog bg-paper-white p-4 transition hover:-translate-y-0.5 hover:border-accent/40 hover:shadow-[0_18px_40px_-24px_rgb(15_30_61/0.45)]';
    @endphp

    <x-daftar.data id="data-kegiatan" :nilai="$awal" />

    <div x-data="daftar({ sumber: 'data-kegiatan', bukanFilter: ['bagian'], gulirKe: 'hasil' })">
        <section class="relative isolate overflow-hidden bg-navy text-paper-white">
            <div class="hero-dots pointer-events-none absolute inset-0" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -top-32 right-[-5%] size-[420px] rounded-full bg-primary/50 blur-3xl" aria-hidden="true"></div>
            <div class="relative mx-auto max-w-page px-4 py-10 sm:px-6">
                <p class="text-sm font-semibold text-accent">Mahasiswa</p>
                <h1 class="mt-2 text-heading-sm font-bold tracking-[-0.5px] sm:text-heading">Kegiatan saya</h1>
                <p class="mt-1 text-white/70">Status pendaftaran magang, lomba, dan beasiswa, serta permintaan bimbingan ke dosen. Klik kegiatan untuk melihat detailnya.</p>
                <div class="mt-6 flex flex-wrap gap-2 text-sm" aria-label="Jumlah pendaftaran per status">
                    @foreach (\App\Support\StatusPeninjauan::LABEL as $s => $nama)
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 ring-1 ring-white/15">
                            <span class="size-2 rounded-full {{ $titik[$s] }}" aria-hidden="true"></span>
                            {{ $nama }} <span class="font-bold" x-text="tambahan.jumlah_status?.['{{ $s }}'] ?? 0">{{ $jumlahStatus[$s] ?? 0 }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Daftar (kiri) + panel detail (kanan, layar lebar). Di HP, kartu membuka halaman detail. --}}
        <section class="bg-[#f7f8fc]">
            <div class="mx-auto grid max-w-page grid-cols-1 gap-6 px-4 py-8 sm:px-6 md:py-10 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:items-start"
                 x-data="panelDetail(@js($kunciAwal))"
                 @daftar-dimuat="setelahDimuat($event.detail.items, (i) => i.kunci)">
                <div class="min-w-0 scroll-mt-24" id="hasil">
                    {{-- Pendaftaran atau bimbingan --}}
                    <div class="inline-flex gap-1 rounded-full bg-paper-white p-1 ring-1 ring-fog" role="group" aria-label="Jenis kegiatan">
                        @foreach (['pendaftaran' => 'Pendaftaran', 'bimbingan' => 'Bimbingan dosen'] as $kunci => $nama)
                            <button type="button" @click="filter.bagian = '{{ $kunci }}'"
                                    :aria-pressed="(filter.bagian === '{{ $kunci }}').toString()" aria-pressed="{{ $filter['bagian'] === $kunci ? 'true' : 'false' }}"
                                    class="group/tab inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold text-graphite transition hover:text-carbon aria-pressed:bg-navy aria-pressed:text-paper-white">
                                {{ $nama }}
                                <span class="rounded-full bg-mist px-2 py-0.5 text-[11px] text-graphite group-aria-pressed/tab:bg-accent group-aria-pressed/tab:text-navy"
                                      x-text="tambahan.jumlah_bagian?.['{{ $kunci }}'] ?? 0">{{ $awal['tambahan']['jumlah_bagian'][$kunci] ?? 0 }}</span>
                            </button>
                        @endforeach
                    </div>

                    <form @submit.prevent="muat(1)" role="search" class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center">
                        <div class="flex flex-1 items-center gap-2 rounded-full bg-paper-white px-4 ring-1 ring-fog">
                            <svg class="size-5 shrink-0 text-ash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                            <label for="q" class="sr-only">Judul kegiatan atau nama dosen</label>
                            <input id="q" type="search" maxlength="100" x-model="filter.q" value="{{ $filter['q'] }}" autocomplete="off"
                                   :placeholder="filter.bagian === 'bimbingan' ? 'Cari judul kegiatan atau dosen' : 'Cari judul kegiatan'" placeholder="Cari judul kegiatan"
                                   class="w-full bg-transparent py-2.5 placeholder:text-ash focus:outline-none">
                        </div>
                        <label for="status" class="sr-only">Status</label>
                        <select id="status" x-model="filter.status" x-combobox class="h-11 rounded-full bg-paper-white px-4 text-sm font-medium text-carbon ring-1 ring-fog sm:w-40">
                            <option value="">Semua status</option>
                            @foreach (\App\Support\StatusPeninjauan::LABEL as $s => $nama)
                                <option value="{{ $s }}" @selected($filter['status'] === $s)>{{ $nama }}</option>
                            @endforeach
                        </select>
                        <x-daftar.urut :filter="$filter" class="h-11 rounded-full! border-0! bg-paper-white! px-4 text-carbon! ring-1 ring-fog sm:w-40"
                                       :pilihan="['dikirim:desc' => 'Terbaru', 'dikirim:asc' => 'Terlama']" />
                    </form>

                    <x-daftar.status />

                    <div class="mt-4 space-y-2.5 transition-opacity" :class="memuat && 'opacity-60'" :aria-busy="memuat.toString()">
                        <template x-for="k in items" :key="filter.bagian + k.id">
                            <a :href="k.url"
                               @click="k.url && buka($event, k.kunci)"
                               :class="{ 'pointer-events-none opacity-70': ! k.url, 'lg:border-accent lg:ring-2 lg:ring-accent/30': k.url && aktif === k.kunci }"
                               :aria-current="k.url && aktif === k.kunci ? 'true' : null"
                               class="{{ $kartu }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        {{-- Pendaftaran: kategori + judul + penyelenggara. Bimbingan: dosen + kategori: judul. --}}
                                        <template x-if="k.dosen === undefined">
                                            <div>
                                                <p class="text-[11px] font-bold tracking-wider text-accent-dark uppercase" x-text="k.kategori"></p>
                                                <p class="mt-0.5 font-semibold text-carbon group-hover:text-accent-dark" x-text="k.judul"></p>
                                                <p class="truncate text-caption text-graphite" x-show="k.penyelenggara" x-text="k.penyelenggara"></p>
                                            </div>
                                        </template>
                                        <template x-if="k.dosen !== undefined">
                                            <div>
                                                <p class="font-semibold text-carbon group-hover:text-accent-dark" x-text="k.dosen"></p>
                                                <p class="truncate text-caption text-graphite" x-text="`${k.kategori}: ${k.judul}`"></p>
                                            </div>
                                        </template>
                                    </div>
                                    <span :data-varian="k.status.varian" class="{{ $lencana }}">
                                        <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span><span x-text="k.status.label"></span>
                                    </span>
                                </div>
                                <p class="mt-2 text-caption text-graphite" x-text="k.waktu"></p>
                                <p x-show="k.catatan" class="mt-2 line-clamp-2 rounded-lg bg-[#f7f8fc] px-2.5 py-1.5 text-sm">
                                    <span class="font-semibold" x-text="k.dosen === undefined ? 'Catatan:' : 'Catatan dosen:'"></span> <span x-text="k.catatan"></span>
                                </p>
                            </a>
                        </template>

                        <div class="rounded-3xl border border-dashed border-fog bg-paper-white px-6 py-10 text-center" x-show="items.length === 0" @if (count($awal['data'])) x-cloak @endif>
                            <div x-show="adaFilter" @if (! $adaFilter) x-cloak @endif>
                                <p class="font-semibold">Tidak ada kegiatan yang cocok.</p>
                                <button type="button" @click="aturUlang()" class="mt-2 text-sm font-medium text-accent-dark hover:underline">Hapus pencarian</button>
                            </div>
                            <div x-show="! adaFilter && filter.bagian === 'pendaftaran'" @if ($adaFilter || $filter['bagian'] !== 'pendaftaran') x-cloak @endif>
                                <p class="font-semibold">Belum ada pendaftaran.</p>
                                <p class="mt-1 text-sm text-graphite">Cari kegiatan yang bertanda "Daftar di CampusLink", lalu unggah CV Anda.</p>
                                <div class="mt-4 flex flex-wrap justify-center gap-2">
                                    <a href="{{ route('magang.index') }}" class="btn btn-primary px-5 py-2.5">Lihat magang</a>
                                    <a href="{{ route('lomba.index') }}" class="btn btn-outline px-5 py-2.5">Lomba</a>
                                    <a href="{{ route('beasiswa.index') }}" class="btn btn-outline px-5 py-2.5">Beasiswa</a>
                                </div>
                            </div>
                            <div x-show="! adaFilter && filter.bagian === 'bimbingan'" @if ($adaFilter || $filter['bagian'] !== 'bimbingan') x-cloak @endif>
                                <p class="font-semibold">Belum ada permintaan bimbingan.</p>
                                <p class="mt-1 text-sm text-graphite">Setelah Anda diterima di magang atau lomba, pilih dosen pembimbing di detail kegiatannya.</p>
                            </div>
                        </div>
                    </div>

                    <x-daftar.halaman :meta="$awal['meta']" />
                </div>

                {{-- Panel detail: status, CV, batal, dan dosen pembimbing tampil di sini tanpa pindah halaman --}}
                <aside class="hidden lg:sticky lg:top-24 lg:block" aria-label="Detail kegiatan" aria-live="polite" x-show="items.some((i) => i.url)" @if (! $detail) x-cloak @endif>
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

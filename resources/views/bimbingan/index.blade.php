@extends('layouts.app')

@section('title', 'Bimbingan')

@section('content')
    @php
        $filter = $awal['filter'];
        $kosong = [
            'menunggu' => 'Tidak ada permintaan bimbingan yang menunggu.',
            'diterima' => 'Belum ada bimbingan diterima.',
            'ditolak' => 'Belum ada bimbingan ditolak.',
        ];
        // Lencana status: warna mengikuti data-varian (warning/success/danger), selalu disertai label.
        $lencana = 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold
            data-[varian=warning]:bg-accent-wash data-[varian=warning]:text-accent-dark
            data-[varian=success]:bg-mint-wash data-[varian=success]:text-[#15803d]
            data-[varian=danger]:bg-[#fee2e2] data-[varian=danger]:text-[#b91c1c]';
    @endphp

    <x-daftar.data id="data-bimbingan" :nilai="$awal" />

    <div x-data="daftar({ sumber: 'data-bimbingan', bukanFilter: ['status'], gulirKe: 'hasil' })">
        <section class="relative isolate overflow-hidden bg-navy text-paper-white">
            <div class="hero-dots pointer-events-none absolute inset-0" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -top-32 right-[-5%] size-[420px] rounded-full bg-primary/50 blur-3xl" aria-hidden="true"></div>
            <div class="relative mx-auto max-w-page px-4 py-10 sm:px-6">
                <p class="text-sm font-semibold text-accent">Dosen</p>
                <h1 class="mt-2 text-heading-sm font-bold tracking-[-0.5px] sm:text-heading">Permintaan bimbingan</h1>
                <p class="mt-1 text-white/70">Mahasiswa yang memilih Anda sebagai pembimbing magang atau lomba. Terima bila Anda bersedia membimbing.</p>
                <a href="{{ route('profil.show') }}" class="mt-3 inline-block text-sm font-medium text-white/80 underline-offset-2 hover:underline">Atur kesediaan membimbing di profil &rarr;</a>

                <div class="mt-6 inline-flex flex-wrap gap-1 rounded-full bg-white/10 p-1 ring-1 ring-white/15" role="group" aria-label="Status bimbingan">
                    @foreach (\App\Support\StatusPeninjauan::LABEL as $kunci => $nama)
                        <button type="button" @click="filter.status = '{{ $kunci }}'"
                                :aria-pressed="(filter.status === '{{ $kunci }}').toString()" aria-pressed="{{ $filter['status'] === $kunci ? 'true' : 'false' }}"
                                class="group/tab inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold text-white/75 transition hover:bg-white/10 hover:text-paper-white aria-pressed:bg-accent aria-pressed:text-navy aria-pressed:hover:bg-accent">
                            {{ $nama }}
                            <span class="rounded-full bg-white/15 px-2 py-0.5 text-[11px] group-aria-pressed/tab:bg-navy group-aria-pressed/tab:text-paper-white"
                                  x-text="tambahan.jumlah?.['{{ $kunci }}'] ?? 0">{{ $jumlah[$kunci] ?? 0 }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="bg-[#f7f8fc]">
            <div class="mx-auto max-w-page scroll-mt-20 px-4 py-8 sm:px-6 md:py-10" id="hasil">
                <form @submit.prevent="muat(1)" role="search" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="flex flex-1 items-center gap-2 rounded-full bg-paper-white px-4 ring-1 ring-fog">
                        <svg class="size-5 shrink-0 text-ash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                        <label for="q" class="sr-only">Nama mahasiswa</label>
                        <input id="q" type="search" maxlength="100" x-model="filter.q" value="{{ $filter['q'] }}" placeholder="Cari nama mahasiswa" autocomplete="off"
                               class="w-full bg-transparent py-2.5 placeholder:text-ash focus:outline-none">
                    </div>
                    <x-daftar.urut :filter="$filter" class="h-11 rounded-full! border-0! bg-paper-white! px-4 text-carbon! ring-1 ring-fog sm:w-48"
                                   :pilihan="['diajukan:desc' => 'Diajukan terbaru', 'diajukan:asc' => 'Diajukan paling awal']" />
                </form>

                <x-daftar.status />

                <div class="mt-6 grid gap-4 transition-opacity lg:grid-cols-2" :class="memuat && 'opacity-60'" :aria-busy="memuat.toString()">
                    <template x-for="b in items" :key="b.id">
                        <article class="flex flex-col rounded-2xl border border-fog bg-paper-white p-5 sm:p-6" x-data="{ catatan: b.catatan }">
                            <div class="flex items-start gap-4">
                                <x-daftar.foto data="b.mahasiswa" ukuran="size-12" />
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <p class="font-semibold text-carbon" x-text="b.mahasiswa.nama"></p>
                                        <span :data-varian="b.status.varian" class="{{ $lencana }}">
                                            <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span><span x-text="b.status.label"></span>
                                        </span>
                                    </div>
                                    <p class="text-sm text-graphite" x-show="b.mahasiswa.keterangan" x-text="b.mahasiswa.keterangan"></p>
                                    <p class="mt-0.5 text-sm break-words text-graphite" x-text="b.mahasiswa.kontak"></p>
                                </div>
                            </div>

                            <div class="mt-4 rounded-xl bg-[#f7f8fc] p-3 text-sm">
                                <span class="font-semibold text-accent-dark" x-text="b.kategori"></span>
                                <template x-if="b.informasi">
                                    <span>
                                        <a :href="b.informasi.url" class="font-semibold text-carbon hover:text-accent-dark" x-text="b.informasi.judul"></a>
                                        <span class="block text-graphite" x-text="b.informasi.penyelenggara"></span>
                                    </span>
                                </template>
                                <span x-show="! b.informasi" class="text-graphite">Informasi sudah dihapus</span>
                            </div>

                            <p x-show="b.pesan" class="mt-3 text-sm"><span class="font-semibold">Pesan mahasiswa:</span> <span class="text-graphite" x-text="b.pesan"></span></p>

                            <p x-show="b.bukti" class="mt-2 text-sm">
                                <a :href="b.bukti" target="_blank" class="inline-flex items-center gap-1 font-medium text-accent-dark hover:underline">
                                    Lihat bukti diterima &rarr;
                                </a>
                            </p>
                            <p x-show="! b.bukti && b.informasi?.lewat_campuslink" class="mt-2 text-sm font-medium text-[#16a34a]">Sudah diterima oleh penyelenggara</p>

                            <div x-show="b.mahasiswa.keahlian.length > 0" class="mt-3 flex flex-wrap gap-1.5">
                                <template x-for="k in b.mahasiswa.keahlian" :key="k">
                                    <span class="rounded-full bg-mist px-2.5 py-1 text-caption font-medium text-graphite" x-text="k"></span>
                                </template>
                            </div>

                            <div class="mt-auto pt-4">
                                <label :for="`catatan-${b.id}`" class="sr-only">Catatan untuk mahasiswa</label>
                                <textarea :id="`catatan-${b.id}`" rows="2" maxlength="1000" x-model="catatan" class="field text-sm" placeholder="Catatan untuk mahasiswa (opsional), misalnya jadwal bimbingan"></textarea>
                                <div class="mt-3 grid grid-cols-2 gap-2">
                                    <form method="POST" :action="b.url.terima">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="catatan" :value="catatan">
                                        <button type="submit" class="btn w-full bg-[#16a34a] py-2.5 font-semibold text-paper-white hover:bg-[#15803d]" x-text="b.status.kunci === 'diterima' ? 'Simpan catatan' : 'Terima'">Terima</button>
                                    </form>
                                    <form method="POST" :action="b.url.tolak">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="catatan" :value="catatan">
                                        <button type="submit" class="btn w-full border border-[#fecaca] bg-paper-white py-2.5 font-semibold text-[#b91c1c] hover:bg-[#fef2f2]">Tolak</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    </template>

                    <div class="rounded-3xl border border-dashed border-fog bg-paper-white px-6 py-14 text-center lg:col-span-2" x-show="items.length === 0" @if (count($awal['data'])) x-cloak @endif>
                        <p class="font-semibold" x-text="adaFilter ? 'Tidak ada mahasiswa yang cocok.' : {{ \Illuminate\Support\Js::from($kosong) }}[filter.status]">{{ $filter['q'] !== '' ? 'Tidak ada mahasiswa yang cocok.' : $kosong[$filter['status']] }}</p>
                        <p class="mt-1 text-sm text-graphite">Mahasiswa hanya bisa memilih Anda bila Anda menyatakan bersedia membimbing magang atau lomba di profil.</p>
                    </div>
                </div>

                <x-daftar.halaman :meta="$awal['meta']" />
            </div>
        </section>
    </div>
@endsection

@extends('layouts.panel')

@section('title', 'Bimbingan')

@section('content')
    @php
        $filter = $awal['filter'];
        $kosong = [
            'menunggu' => 'Tidak ada permintaan bimbingan yang menunggu.',
            'diterima' => 'Belum ada bimbingan diterima.',
            'ditolak' => 'Belum ada bimbingan ditolak.',
        ];
    @endphp

    <x-admin.page-header title="Permintaan bimbingan"
                         description="Mahasiswa yang memilih Anda sebagai pembimbing magang atau lomba. Terima bila Anda bersedia membimbing.">
        <x-slot:actions>
            <x-admin.button variant="outline" :href="route('profil.show')">Atur kesediaan membimbing</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-daftar.data id="data-bimbingan" :nilai="$awal" />

    <div class="scroll-mt-20" id="hasil" x-data="daftar({ sumber: 'data-bimbingan', bukanFilter: ['status'], gulirKe: 'hasil' })">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <x-admin.tabs label="Status bimbingan" model="status" :jumlah="$jumlah" :items="\App\Support\StatusPeninjauan::LABEL" />

            <form @submit.prevent="muat(1)" role="search" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="relative sm:w-72">
                    <x-admin.icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                    <label for="q" class="sr-only">Nama mahasiswa</label>
                    <input id="q" type="search" maxlength="100" x-model="filter.q" value="{{ $filter['q'] }}" placeholder="Cari nama mahasiswa" autocomplete="off"
                           class="h-10 w-full rounded-md border border-input bg-card pr-3 pl-9 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none">
                </div>
                <x-daftar.urut :filter="$filter" class="sm:w-48"
                               :pilihan="['diajukan:desc' => 'Diajukan terbaru', 'diajukan:asc' => 'Diajukan paling awal']" />
            </form>
        </div>

        <x-daftar.status />

        <div class="mt-6 grid gap-4 transition-opacity lg:grid-cols-2" :class="memuat && 'opacity-60'" :aria-busy="memuat.toString()">
            <template x-for="b in items" :key="b.id">
                <article class="flex flex-col rounded-xl border border-border bg-card p-5 text-foreground shadow-sm" x-data="{ catatan: b.catatan }">
                    <div class="flex items-start gap-4">
                        <x-daftar.foto data="b.mahasiswa" ukuran="size-11" />
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <p class="font-semibold" x-text="b.mahasiswa.nama"></p>
                                <x-admin.badge ::data-varian="b.status.varian" dot><span x-text="b.status.label"></span></x-admin.badge>
                            </div>
                            <p class="text-sm text-muted-foreground" x-show="b.mahasiswa.keterangan" x-text="b.mahasiswa.keterangan"></p>
                            <p class="mt-0.5 text-sm break-words text-muted-foreground" x-text="b.mahasiswa.kontak"></p>
                        </div>
                    </div>

                    <div class="mt-4 rounded-lg bg-muted p-3 text-sm">
                        <span class="font-semibold text-accent-dark dark:text-accent" x-text="b.kategori"></span>
                        <template x-if="b.informasi">
                            <span>
                                <a :href="b.informasi.url" class="font-medium hover:underline" x-text="b.informasi.judul"></a>
                                <span class="block text-muted-foreground" x-text="b.informasi.penyelenggara"></span>
                            </span>
                        </template>
                        <span x-show="! b.informasi" class="text-muted-foreground">Informasi sudah dihapus</span>
                    </div>

                    <p x-show="b.pesan" class="mt-3 text-sm"><span class="font-medium">Pesan mahasiswa:</span> <span class="text-muted-foreground" x-text="b.pesan"></span></p>

                    <p x-show="b.bukti" class="mt-2 text-sm">
                        <a :href="b.bukti" target="_blank" class="inline-flex items-center gap-1 font-medium text-accent-dark hover:underline dark:text-accent">
                            Lihat bukti diterima <x-admin.icon name="external" class="size-3.5" />
                        </a>
                    </p>
                    <p x-show="! b.bukti && b.informasi?.lewat_campuslink" class="mt-2 flex items-center gap-1.5 text-sm font-medium text-emerald-700 dark:text-emerald-400">
                        <x-admin.icon name="check" class="size-4" /> Sudah diterima oleh penyelenggara
                    </p>

                    <div x-show="b.mahasiswa.keahlian.length > 0" class="mt-3 flex flex-wrap gap-1.5">
                        <template x-for="k in b.mahasiswa.keahlian" :key="k">
                            <x-admin.badge><span x-text="k"></span></x-admin.badge>
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
                                <x-admin.button type="submit" variant="success" class="w-full" x-text="b.status.kunci === 'diterima' ? 'Simpan catatan' : 'Terima'">Terima</x-admin.button>
                            </form>
                            <form method="POST" :action="b.url.tolak">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="catatan" :value="catatan">
                                <x-admin.button type="submit" variant="danger-outline" class="w-full">Tolak</x-admin.button>
                            </form>
                        </div>
                    </div>
                </article>
            </template>

            <div class="rounded-xl border border-dashed border-border bg-card text-foreground lg:col-span-2" x-show="items.length === 0" @if (count($awal['data'])) x-cloak @endif>
                <x-admin.empty-state :title="$filter['q'] !== '' ? 'Tidak ada mahasiswa yang cocok.' : $kosong[$filter['status']]"
                                     x-title="adaFilter ? 'Tidak ada mahasiswa yang cocok.' : {{ \Illuminate\Support\Js::from($kosong) }}[filter.status]" icon="users">
                    Mahasiswa hanya bisa memilih Anda bila Anda menyatakan bersedia membimbing magang atau lomba di profil.
                </x-admin.empty-state>
            </div>
        </div>

        <x-daftar.halaman :meta="$awal['meta']" />
    </div>
@endsection

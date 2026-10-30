@extends('layouts.panel')

@section('title', 'Direktori')

@section('content')
    @php
        $f = fn ($kunci) => $filter[$kunci] ?? null;
        // Kunci '' = semua jenis (kunci null di array PHP berubah menjadi '').
        $daftarJenis = ['' => 'Semua', 'mahasiswa' => 'Mahasiswa', 'dosen' => 'Dosen'];
        $jenisAktif = (string) ($f('jenis') ?? '');
        $adaFilter = collect($filter)->except(['urut', 'arah'])->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
        $kelasPilih = 'h-10 w-full rounded-md border border-input bg-card px-3 text-sm font-medium text-foreground focus:border-ring focus:outline-none';
        $kelasLabel = 'mb-1.5 block text-xs font-medium text-muted-foreground';
    @endphp

    <x-admin.page-header title="Direktori mahasiswa dan dosen"
                         description="Temukan rekan tim dan dosen pembimbing. Hanya profil yang diizinkan pemiliknya yang tampil." />

    <x-daftar.data id="data-direktori" :nilai="$awal" />

    <div x-data="daftar({ sumber: 'data-direktori', gulirKe: 'hasil' })">
        <x-admin.card>
            <form @submit.prevent="muat(1)" role="search">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    {{-- Pilihan jenis profil --}}
                    <div class="inline-flex max-w-full flex-wrap gap-1 rounded-lg bg-muted p-1" role="group" aria-label="Jenis profil">
                        @foreach ($daftarJenis as $kunci => $nama)
                            <button type="button" @click="filter.jenis = '{{ $kunci }}'"
                                    :aria-pressed="(filter.jenis === '{{ $kunci }}').toString()" aria-pressed="{{ $jenisAktif === (string) $kunci ? 'true' : 'false' }}"
                                    class="rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground aria-pressed:bg-card aria-pressed:text-foreground aria-pressed:shadow-sm">{{ $nama }}</button>
                        @endforeach
                    </div>

                    <div class="relative flex-1">
                        <x-admin.icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <label for="q" class="sr-only">Nama</label>
                        <input id="q" type="search" maxlength="100" x-model="filter.q" value="{{ $f('q') }}" placeholder="Cari berdasarkan nama" autocomplete="off"
                               class="h-10 w-full rounded-md border border-input bg-card pr-9 pl-9 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none">
                        <span x-cloak x-show="memuat" class="absolute top-1/2 right-3 size-4 -translate-y-1/2 animate-spin rounded-full border-2 border-accent/30 border-t-accent" role="status" aria-label="Mencari"></span>
                    </div>
                    <x-daftar.urut :filter="$awal['filter']" class="lg:w-44" :pilihan="['nama:asc' => 'Nama A-Z', 'nama:desc' => 'Nama Z-A']" />
                </div>

                <div class="mt-4 grid gap-3 border-t border-border pt-4 sm:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <label for="keahlian" class="{{ $kelasLabel }}">Bidang keahlian</label>
                        <select id="keahlian" x-model="filter.keahlian" x-combobox class="{{ $kelasPilih }}">
                            <option value="">Semua keahlian</option>
                            @foreach ($daftarKeahlian as $k)
                                <option value="{{ $k->id }}" @selected((string) $f('keahlian') === (string) $k->id)>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="jurusan" class="{{ $kelasLabel }}">Jurusan</label>
                        <select id="jurusan" x-model="filter.jurusan" x-combobox class="{{ $kelasPilih }}">
                            <option value="">Semua jurusan</option>
                            @foreach ($daftarJurusan as $j)
                                <option value="{{ $j }}" @selected($f('jurusan') === $j)>{{ $j }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="program_studi" class="{{ $kelasLabel }}">Program studi</label>
                        <select id="program_studi" x-model="filter.program_studi" x-combobox class="{{ $kelasPilih }}">
                            <option value="">Semua program studi</option>
                            @foreach ($daftarProdi as $jurusan => $daftar)
                                <optgroup label="{{ $jurusan }}">
                                    @foreach ($daftar as $prodi)
                                        <option value="{{ $prodi }}" @selected($f('program_studi') === $prodi)>{{ $prodi }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="kelas" class="{{ $kelasLabel }}">Kelas</label>
                        <select id="kelas" x-model="filter.kelas" x-combobox class="{{ $kelasPilih }}">
                            <option value="">Semua kelas</option>
                            @foreach ($daftarKelas as $kelas)
                                <option value="{{ $kelas }}" @selected($f('kelas') === $kelas)>{{ $kelas }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="angkatan" class="{{ $kelasLabel }}">Angkatan</label>
                        <select id="angkatan" x-model="filter.angkatan" x-combobox class="{{ $kelasPilih }}">
                            <option value="">Semua angkatan</option>
                            @foreach ($daftarAngkatan as $tahun)
                                <option value="{{ $tahun }}" @selected((string) $f('angkatan') === (string) $tahun)>{{ $tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <p class="mt-3 text-xs text-muted-foreground" x-show="filter.kelas || filter.angkatan" @if (! $f('kelas') && ! $f('angkatan')) x-cloak @endif>
                    Kelas dan angkatan hanya dimiliki mahasiswa, jadi dosen tidak ikut dalam hasil.
                </p>
            </form>
        </x-admin.card>

        @if ($errors->any())
            <p class="mt-3 text-sm text-red-600 dark:text-red-400" role="alert">{{ $errors->first() }}</p>
        @endif
        <p x-cloak x-show="galat" class="mt-3 text-sm text-red-600 dark:text-red-400" role="alert" x-text="galat"></p>

        <x-daftar.status />

        {{-- Hasil --}}
        <div class="mt-6 scroll-mt-20" id="hasil">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm font-medium" aria-live="polite" x-text="`${meta.total} profil ditemukan`">{{ $awal['meta']['total'] }} profil ditemukan</p>
                <button type="button" x-show="adaFilter" @if (! $adaFilter) x-cloak @endif @click="aturUlang()"
                        class="text-sm font-medium text-muted-foreground hover:text-foreground">Atur ulang</button>
            </div>

            <div class="mt-3 grid gap-4 transition-opacity md:grid-cols-2 xl:grid-cols-3" :class="memuat && 'opacity-60'" :aria-busy="memuat.toString()">
                <template x-for="user in items" :key="user.id">
                    @include('direktori._kartu')
                </template>

                <div class="rounded-xl border border-dashed border-border bg-card text-foreground md:col-span-2 xl:col-span-3" x-show="items.length === 0" @if (count($awal['data'])) x-cloak @endif>
                    <div x-show="adaFilter" @if (! $adaFilter) x-cloak @endif>
                        <x-admin.empty-state title="Tidak ada profil yang cocok." icon="users">Longgarkan filter, atau atur ulang untuk melihat semua.</x-admin.empty-state>
                        <div class="-mt-6 flex justify-center pb-10">
                            <x-admin.button variant="outline" @click="aturUlang()">Atur ulang filter</x-admin.button>
                        </div>
                    </div>
                    <div x-show="! adaFilter" @if ($adaFilter) x-cloak @endif>
                        <x-admin.empty-state title="Belum ada profil di direktori." icon="users">Profil muncul setelah pemiliknya mengizinkan.</x-admin.empty-state>
                    </div>
                </div>
            </div>

            <x-daftar.halaman :meta="$awal['meta']" />
        </div>
    </div>
@endsection

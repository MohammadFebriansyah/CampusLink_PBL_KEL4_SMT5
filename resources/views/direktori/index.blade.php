@extends('layouts.app')

@section('title', 'Direktori')

@section('content')
    @php
        $f = fn ($kunci) => $filter[$kunci] ?? null;
        // Kunci '' = semua jenis (kunci null di array PHP berubah menjadi '').
        $daftarJenis = ['' => 'Semua', 'mahasiswa' => 'Mahasiswa', 'dosen' => 'Dosen'];
        $jenisAktif = (string) ($f('jenis') ?? '');
        $adaFilter = collect($filter)->except(['urut', 'arah'])->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
        $kelasPilih = 'w-full rounded-xl border border-fog bg-paper-white px-3 pt-6 pb-2 text-sm font-medium text-carbon focus:border-accent focus:outline-none';
        $kelasLabel = 'pointer-events-none absolute top-2 left-3 z-10 text-[11px] font-semibold tracking-wide text-ash uppercase';
    @endphp

    <x-daftar.data id="data-direktori" :nilai="$awal" />

    <div x-data="daftar({ sumber: 'data-direktori', gulirKe: 'hasil' })">
        {{-- Kepala halaman: pita navy, pilihan jenis profil, dan formulir pencarian --}}
        <section class="relative isolate overflow-hidden bg-navy text-paper-white">
            <div class="hero-dots pointer-events-none absolute inset-0" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -top-32 right-[-5%] size-[420px] rounded-full bg-primary/50 blur-3xl" aria-hidden="true"></div>

            <div class="relative mx-auto max-w-page px-4 pt-10 pb-12 sm:px-6 md:pt-14 md:pb-16">
                <nav class="flex items-center gap-2 text-sm text-white/60" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="hover:text-paper-white">Beranda</a>
                    <span aria-hidden="true">/</span>
                    <span class="text-paper-white">Direktori</span>
                </nav>

                <div class="mt-6 flex items-start gap-4">
                    <span class="hidden size-14 shrink-0 items-center justify-center rounded-2xl bg-accent text-navy sm:flex">
                        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5a6.5 6.5 0 0 1 3.5 5.5"/></svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-heading font-bold tracking-[-0.8px] sm:text-heading-lg">Direktori mahasiswa dan dosen</h1>
                        <p class="mt-2 text-white/70">Temukan rekan tim dan dosen pembimbing. Hanya profil yang diizinkan pemiliknya yang tampil.</p>
                    </div>
                </div>

                {{-- Pilihan jenis profil --}}
                <div class="mt-8 inline-flex gap-1 rounded-full bg-white/10 p-1 ring-1 ring-white/15" role="group" aria-label="Jenis profil">
                    @foreach ($daftarJenis as $kunci => $nama)
                        <button type="button" @click="filter.jenis = '{{ $kunci }}'"
                                :aria-pressed="(filter.jenis === '{{ $kunci }}').toString()" aria-pressed="{{ $jenisAktif === (string) $kunci ? 'true' : 'false' }}"
                                class="rounded-full px-4 py-2 text-sm font-semibold text-white/75 transition hover:bg-white/10 hover:text-paper-white aria-pressed:bg-accent aria-pressed:text-navy aria-pressed:hover:bg-accent">{{ $nama }}</button>
                    @endforeach
                </div>

                <form @submit.prevent="muat(1)" role="search"
                      class="mt-4 rounded-3xl bg-paper-white p-3 text-carbon shadow-[0_20px_50px_-20px_rgb(0_0_0/0.5)] sm:p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <div class="flex flex-1 items-center gap-2 rounded-full bg-mist px-4">
                            <svg class="size-5 shrink-0 text-ash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                            <label for="q" class="sr-only">Nama</label>
                            <input id="q" type="search" maxlength="100" x-model="filter.q" value="{{ $f('q') }}" placeholder="Cari berdasarkan nama" autocomplete="off"
                                   class="w-full bg-transparent py-3 text-body placeholder:text-ash focus:outline-none">
                            <span x-cloak x-show="memuat" class="size-4 shrink-0 animate-spin rounded-full border-2 border-accent/30 border-t-accent" role="status" aria-label="Mencari"></span>
                        </div>
                        <x-daftar.urut :filter="$awal['filter']" class="rounded-full! border-0! bg-mist! px-4 py-3 text-carbon! sm:w-44"
                                       :pilihan="['nama:asc' => 'Nama A-Z', 'nama:desc' => 'Nama Z-A']" />
                    </div>

                    <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                        <div class="relative">
                            <label for="keahlian" class="{{ $kelasLabel }}">Bidang keahlian</label>
                            <select id="keahlian" x-model="filter.keahlian" x-combobox class="{{ $kelasPilih }}">
                                <option value="">Semua keahlian</option>
                                @foreach ($daftarKeahlian as $k)
                                    <option value="{{ $k->id }}" @selected((string) $f('keahlian') === (string) $k->id)>{{ $k->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="relative">
                            <label for="jurusan" class="{{ $kelasLabel }}">Jurusan</label>
                            <select id="jurusan" x-model="filter.jurusan" x-combobox class="{{ $kelasPilih }}">
                                <option value="">Semua jurusan</option>
                                @foreach ($daftarJurusan as $j)
                                    <option value="{{ $j }}" @selected($f('jurusan') === $j)>{{ $j }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="relative">
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

                        <div class="relative">
                            <label for="kelas" class="{{ $kelasLabel }}">Kelas</label>
                            <select id="kelas" x-model="filter.kelas" x-combobox class="{{ $kelasPilih }}">
                                <option value="">Semua kelas</option>
                                @foreach ($daftarKelas as $kelas)
                                    <option value="{{ $kelas }}" @selected($f('kelas') === $kelas)>{{ $kelas }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="relative">
                            <label for="angkatan" class="{{ $kelasLabel }}">Angkatan</label>
                            <select id="angkatan" x-model="filter.angkatan" x-combobox class="{{ $kelasPilih }}">
                                <option value="">Semua angkatan</option>
                                @foreach ($daftarAngkatan as $tahun)
                                    <option value="{{ $tahun }}" @selected((string) $f('angkatan') === (string) $tahun)>{{ $tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <p class="mt-3 px-1 text-caption text-graphite" x-show="filter.kelas || filter.angkatan" @if (! $f('kelas') && ! $f('angkatan')) x-cloak @endif>
                        Kelas dan angkatan hanya dimiliki mahasiswa, jadi dosen tidak ikut dalam hasil.
                    </p>
                </form>

                @if ($errors->any())
                    <p class="mt-3 text-sm text-[#fdba74]" role="alert">{{ $errors->first() }}</p>
                @endif
                <p x-cloak x-show="galat" class="mt-3 text-sm text-[#fdba74]" role="alert" x-text="galat"></p>
            </div>
        </section>

        {{-- Hasil --}}
        <section class="bg-[#f7f8fc]">
            <div class="mx-auto max-w-page scroll-mt-20 px-4 py-10 sm:px-6 md:py-12" id="hasil">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-carbon" aria-live="polite" x-text="`${meta.total} profil ditemukan`">{{ $awal['meta']['total'] }} profil ditemukan</p>
                    <button type="button" x-show="adaFilter" @if (! $adaFilter) x-cloak @endif @click="aturUlang()"
                            class="text-sm font-medium text-accent-dark hover:underline">Atur ulang</button>
                </div>

                <div class="mt-5 grid gap-4 transition-opacity md:grid-cols-2 xl:grid-cols-3" :class="memuat && 'opacity-60'" :aria-busy="memuat.toString()">
                    <template x-for="user in items" :key="user.id">
                        @include('direktori._kartu')
                    </template>

                    <div class="rounded-3xl border border-dashed border-fog bg-paper-white px-6 py-14 text-center md:col-span-2 xl:col-span-3" x-show="items.length === 0" @if (count($awal['data'])) x-cloak @endif>
                        <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-accent-wash text-accent-dark">
                            <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5a6.5 6.5 0 0 1 3.5 5.5"/></svg>
                        </span>
                        <div x-show="adaFilter" @if (! $adaFilter) x-cloak @endif>
                            <p class="mt-4 text-subheading font-semibold">Tidak ada profil yang cocok.</p>
                            <p class="mt-1 text-graphite">Longgarkan filter, atau atur ulang untuk melihat semua.</p>
                            <button type="button" @click="aturUlang()" class="btn btn-outline mt-5">Atur ulang filter</button>
                        </div>
                        <div x-show="! adaFilter" @if ($adaFilter) x-cloak @endif>
                            <p class="mt-4 text-subheading font-semibold">Belum ada profil di direktori.</p>
                            <p class="mt-1 text-graphite">Profil muncul setelah pemiliknya mengizinkan.</p>
                        </div>
                    </div>
                </div>

                <x-daftar.halaman :meta="$awal['meta']" />
            </div>
        </section>
    </div>
@endsection

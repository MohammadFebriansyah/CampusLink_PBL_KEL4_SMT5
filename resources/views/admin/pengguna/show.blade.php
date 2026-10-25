@extends('layouts.panel')

@section('title', $user->name)

@section('content')
    @php
        $warnaPeran = ['mahasiswa' => 'bg-primary-wash text-primary', 'dosen' => 'bg-accent-wash text-accent-dark', 'mitra' => 'bg-[#f3e8ff] text-[#7e22ce]', 'admin' => 'bg-mist text-graphite'];
        $baris = [['Email', $user->email]];
        if ($user->role === 'mahasiswa') {
            $baris[] = ['NIM', $profil->nim];
            $baris[] = ['Kelas dan angkatan', $profil->kelas . ', angkatan ' . $profil->angkatan];
        } elseif ($user->role === 'dosen') {
            $baris[] = ['NIDN', $profil->nidn];
        }
        if ($profil) {
            $baris[] = ['Program studi', $profil->program_studi];
            $baris[] = ['Jurusan', $profil->jurusan];
            $baris[] = ['Kontak', $profil->kontak ?: '-'];
        } elseif ($user->mitra) {
            $baris[] = ['Organisasi', $user->mitra->nama_organisasi];
            $baris[] = ['Status verifikasi', ['pending' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'][$user->mitra->status_verifikasi]];
            $baris[] = ['Kontak', $user->mitra->kontak];
            $baris[] = ['Situs web', $user->mitra->website ?: '-'];
        }
    @endphp
    @include('admin._kepala', ['judul' => $user->name, 'kembali' => [route('admin.pengguna.index'), 'Semua pengguna']])

    <section class="bg-[#f7f8fc]">
        <div class="mx-auto grid max-w-page gap-6 px-4 py-8 sm:px-6 md:py-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)] lg:items-start">
            <div class="space-y-6">
                <div class="rounded-2xl border border-fog bg-paper-white p-5 sm:p-6">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full px-3 py-1 text-caption font-semibold {{ $warnaPeran[$user->role] ?? 'bg-mist text-graphite' }}">{{ ucfirst($user->role) }}</span>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-caption font-semibold {{ $user->status === 'aktif' ? 'bg-mint-wash text-[#15803d]' : 'bg-[#fee2e2] text-[#b91c1c]' }}">
                            <span class="size-1.5 rounded-full {{ $user->status === 'aktif' ? 'bg-[#16a34a]' : 'bg-[#dc2626]' }}" aria-hidden="true"></span>
                            {{ $user->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        @if ($user->terverifikasiGoogle())
                            <span class="rounded-full bg-mint-wash px-3 py-1 text-caption font-semibold text-[#15803d]">Terverifikasi Google</span>
                        @endif
                    </div>

                    <dl class="mt-5 divide-y divide-fog">
                        @foreach ($baris as [$judul, $nilai])
                            <div class="flex flex-col gap-0.5 py-3 sm:flex-row sm:justify-between sm:gap-4">
                                <dt class="text-sm text-graphite">{{ $judul }}</dt>
                                <dd class="font-semibold break-words sm:text-right">{{ $nilai }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                @if (count($user->keahlian) > 0)
                    <div class="rounded-2xl border border-fog bg-paper-white p-5 sm:p-6">
                        <h2 class="font-semibold">Bidang keahlian</h2>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($user->keahlian as $keahlian)
                                <span class="rounded-full bg-primary-wash px-3 py-1 text-sm font-medium text-primary">{{ $keahlian->nama }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div>
        <h2 class="sr-only">Tindakan</h2>
        <div class="space-y-5 rounded-2xl border border-fog bg-paper-white p-5 sm:p-6">
            <p class="text-sm font-semibold tracking-wider text-graphite uppercase">Tindakan</p>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="max-w-md">
                    <p class="font-medium">Ubah email{{ $user->role === 'mahasiswa' ? ' dan NIM' : ($user->role === 'dosen' ? ' dan NIDN' : '') }}</p>
                    <p class="text-sm text-graphite">Data identitas yang tidak bisa diubah pengguna sendiri.</p>
                </div>
                <a href="{{ route('admin.pengguna.edit', $user->id) }}" class="inline-flex items-center rounded-full border border-fog bg-paper-white px-3.5 py-1.5 text-sm font-medium transition hover:border-accent/50 hover:text-accent-dark">Ubah</a>
            </div>

            @if ($user->role === 'mitra' && $user->mitra)
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-fog pt-5">
                    <div class="max-w-md">
                        <p class="font-medium">Verifikasi mitra</p>
                        <p class="text-sm text-graphite">Mitra hanya bisa masuk bila disetujui.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($user->mitra->status_verifikasi !== 'disetujui')
                            @include('partials.aksi', ['url' => route('admin.mitra.setujui', $user->mitra->id), 'tombol' => 'Setujui', 'gaya' => 'utama'])
                        @endif
                        @if ($user->mitra->status_verifikasi !== 'ditolak')
                            @include('partials.aksi', ['url' => route('admin.mitra.tolak', $user->mitra->id), 'tombol' => 'Tolak', 'pertanyaan' => 'Tolak mitra ini?', 'ya' => 'Ya, tolak'])
                        @endif
                    </div>
                </div>
            @endif

            @if ($profil)
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-fog pt-5">
                    <div class="max-w-md">
                        <p class="font-medium">Profil di direktori</p>
                        <p class="text-sm text-graphite">
                            {{ $profil->disembunyikan_admin ? 'Disembunyikan admin. Pemilik tidak bisa menampilkannya kembali.' : ($profil->tampil_di_direktori ? 'Tampil di direktori.' : 'Disembunyikan oleh pemiliknya sendiri.') }}
                        </p>
                    </div>
                    @if ($profil->disembunyikan_admin)
                        @include('partials.aksi', ['url' => route('admin.pengguna.tampilkan', $user->id), 'tombol' => 'Izinkan tampil kembali', 'gaya' => 'utama'])
                    @else
                        @include('partials.aksi', ['url' => route('admin.pengguna.sembunyikan', $user->id), 'tombol' => 'Sembunyikan dari direktori', 'pertanyaan' => 'Sembunyikan profil ini dari direktori?', 'ya' => 'Ya, sembunyikan'])
                    @endif
                </div>
            @endif

            <div class="border-t border-fog pt-5">
                @if ($user->status === 'aktif')
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="max-w-md">
                            <p class="font-medium">Nonaktifkan akun</p>
                            <p class="text-sm text-graphite">Pengguna langsung tidak bisa masuk atau memakai sistem, dan hilang dari direktori. Akun tidak dihapus dan bisa diaktifkan kembali.</p>
                        </div>

                        <div x-data="{ yakin: false }">
                            <button type="button" x-show="! yakin" @click="yakin = true" class="inline-flex items-center rounded-full border border-[#fecaca] bg-paper-white px-3.5 py-1.5 text-sm font-medium text-[#b91c1c] transition hover:bg-[#fef2f2]">Nonaktifkan</button>
                            <form x-cloak x-show="yakin" method="POST" action="{{ route('admin.pengguna.nonaktifkan', $user->id) }}" class="space-y-3">
                                @csrf
                                @method('PATCH')
                                @if ($user->role === 'mitra')
                                    <label class="flex items-start gap-2 text-sm">
                                        <input type="checkbox" name="sembunyikan_informasi" value="1" class="mt-0.5 size-4 accent-[#c2410c]" checked>
                                        Sembunyikan juga semua informasi milik mitra ini
                                    </label>
                                @endif
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="btn bg-[#dc2626] px-3.5 py-1.5 text-paper-white hover:bg-[#b91c1c]">Ya, nonaktifkan</button>
                                    <button type="button" @click="yakin = false" class="px-3 py-1.5 text-sm font-medium hover:underline">Batal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="max-w-md">
                            <p class="font-medium">Akun nonaktif</p>
                            <p class="text-sm text-graphite">Aktifkan kembali agar pengguna bisa masuk.</p>
                        </div>
                        @include('partials.aksi', ['url' => route('admin.pengguna.aktifkan', $user->id), 'tombol' => 'Aktifkan kembali', 'gaya' => 'utama'])
                    </div>
                @endif
            </div>
        </div>
            </div>
        </div>
    </section>
@endsection

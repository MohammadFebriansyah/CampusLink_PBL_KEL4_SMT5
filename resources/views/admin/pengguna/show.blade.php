@extends('layouts.admin')

@section('title', $user->name)

@section('content')
    @php
        $varianPeran = ['mahasiswa' => 'info', 'dosen' => 'warning', 'mitra' => 'violet', 'admin' => 'neutral'];
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

    <x-admin.page-header :title="$user->name" :back="route('admin.pengguna.index')" back-label="Semua pengguna" />

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)] lg:items-start">
        <div class="space-y-6">
            <x-admin.card>
                <div class="flex items-center gap-4">
                    <x-admin.avatar :name="$user->name" class="size-12 text-sm" />
                    <div class="flex flex-wrap items-center gap-2">
                        <x-admin.badge :variant="$varianPeran[$user->role] ?? 'neutral'">{{ ucfirst($user->role) }}</x-admin.badge>
                        <x-admin.badge :variant="$user->status === 'aktif' ? 'success' : 'danger'" dot>{{ $user->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}</x-admin.badge>
                        @if ($user->terverifikasiGoogle())
                            <x-admin.badge variant="success">Terverifikasi Google</x-admin.badge>
                        @endif
                    </div>
                </div>

                <dl class="mt-4 divide-y divide-border">
                    @foreach ($baris as [$judul, $nilai])
                        <div class="flex flex-col gap-0.5 py-3 sm:flex-row sm:justify-between sm:gap-4">
                            <dt class="text-sm text-muted-foreground">{{ $judul }}</dt>
                            <dd class="font-medium break-words sm:text-right">{{ $nilai }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-admin.card>

            @if (count($user->keahlian) > 0)
                <x-admin.card title="Bidang keahlian">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($user->keahlian as $keahlian)
                            <x-admin.badge variant="info">{{ $keahlian->nama }}</x-admin.badge>
                        @endforeach
                    </div>
                </x-admin.card>
            @endif
        </div>

        <x-admin.card title="Tindakan" description="Perubahan berlaku langsung untuk akun ini.">
            <div class="divide-y divide-border [&>*]:py-5 [&>*:first-child]:pt-0 [&>*:last-child]:pb-0">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="max-w-md">
                        <p class="font-medium">Ubah email{{ $user->role === 'mahasiswa' ? ' dan NIM' : ($user->role === 'dosen' ? ' dan NIDN' : '') }}</p>
                        <p class="text-sm text-muted-foreground">Data identitas yang tidak bisa diubah pengguna sendiri.</p>
                    </div>
                    <x-admin.button variant="outline" size="sm" :href="route('admin.pengguna.edit', $user->id)">Ubah</x-admin.button>
                </div>

                @if ($user->role === 'mitra' && $user->mitra)
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="max-w-md">
                            <p class="font-medium">Verifikasi mitra</p>
                            <p class="text-sm text-muted-foreground">Mitra hanya bisa masuk bila disetujui.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($user->mitra->status_verifikasi !== 'disetujui')
                                <x-admin.action :url="route('admin.mitra.setujui', $user->mitra->id)" variant="primary">Setujui</x-admin.action>
                            @endif
                            @if ($user->mitra->status_verifikasi !== 'ditolak')
                                <x-admin.action :url="route('admin.mitra.tolak', $user->mitra->id)" confirm="Tolak mitra ini?" yes="Ya, tolak">Tolak</x-admin.action>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($profil)
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="max-w-md">
                            <p class="font-medium">Profil di direktori</p>
                            <p class="text-sm text-muted-foreground">
                                {{ $profil->disembunyikan_admin ? 'Disembunyikan admin. Pemilik tidak bisa menampilkannya kembali.' : ($profil->tampil_di_direktori ? 'Tampil di direktori.' : 'Disembunyikan oleh pemiliknya sendiri.') }}
                            </p>
                        </div>
                        @if ($profil->disembunyikan_admin)
                            <x-admin.action :url="route('admin.pengguna.tampilkan', $user->id)" variant="primary">Izinkan tampil kembali</x-admin.action>
                        @else
                            <x-admin.action :url="route('admin.pengguna.sembunyikan', $user->id)" confirm="Sembunyikan profil ini dari direktori?" yes="Ya, sembunyikan">Sembunyikan dari direktori</x-admin.action>
                        @endif
                    </div>
                @endif

                @if ($user->status === 'aktif')
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="max-w-md">
                            <p class="font-medium text-red-700 dark:text-red-400">Nonaktifkan akun</p>
                            <p class="text-sm text-muted-foreground">Pengguna langsung tidak bisa masuk atau memakai sistem, dan hilang dari direktori. Akun tidak dihapus dan bisa diaktifkan kembali.</p>
                        </div>

                        <div x-data="{ yakin: false }">
                            <x-admin.button variant="danger-outline" size="sm" x-show="! yakin" @click="yakin = true">Nonaktifkan</x-admin.button>
                            <form x-cloak x-show="yakin" method="POST" action="{{ route('admin.pengguna.nonaktifkan', $user->id) }}"
                                  class="space-y-3 rounded-lg bg-red-50 p-3 ring-1 ring-red-200 dark:bg-red-500/10 dark:ring-red-500/30">
                                @csrf
                                @method('PATCH')
                                @if ($user->role === 'mitra')
                                    <label class="flex items-start gap-2 text-sm">
                                        <input type="checkbox" name="sembunyikan_informasi" value="1" class="mt-0.5 size-4 accent-red-600" checked>
                                        Sembunyikan juga semua informasi milik mitra ini
                                    </label>
                                @endif
                                <div class="flex items-center gap-2">
                                    <x-admin.button type="submit" variant="danger" size="sm">Ya, nonaktifkan</x-admin.button>
                                    <x-admin.button variant="ghost" size="sm" @click="yakin = false">Batal</x-admin.button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="max-w-md">
                            <p class="font-medium">Akun nonaktif</p>
                            <p class="text-sm text-muted-foreground">Aktifkan kembali agar pengguna bisa masuk.</p>
                        </div>
                        <x-admin.action :url="route('admin.pengguna.aktifkan', $user->id)" variant="success">Aktifkan kembali</x-admin.action>
                    </div>
                @endif
            </div>
        </x-admin.card>
    </div>
@endsection

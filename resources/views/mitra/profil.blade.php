@extends('layouts.panel')

@section('title', 'Profil organisasi')

@section('content')
    @include('mitra._kepala', ['judul' => 'Profil organisasi', 'deskripsi' => 'Data ini tampil bersama informasi yang Anda tayangkan.'])

    <section class="bg-[#f7f8fc]">
        <div class="mx-auto max-w-page px-4 py-8 sm:px-6 md:py-10"><div class="max-w-3xl">
        <form method="POST" action="{{ route('mitra.profil.update') }}" class="space-y-5 rounded-2xl border border-fog bg-paper-white p-6 sm:p-8">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-caption font-semibold {{ $mitra->status_verifikasi === 'disetujui' ? 'bg-mint-wash text-[#15803d]' : 'bg-accent-wash text-accent-dark' }}">
                    {{ $mitra->status_verifikasi === 'disetujui' ? 'Terverifikasi' : ($mitra->status_verifikasi === 'pending' ? 'Menunggu verifikasi' : 'Tidak disetujui') }}
                </span>
            </div>
            @csrf
            @method('PUT')

            <dl class="rounded-xl bg-[#f7f8fc] p-4 text-sm">
                <dt class="text-graphite">Email</dt>
                <dd class="font-medium">{{ $user->email }}</dd>
                <p class="mt-2 text-caption text-graphite">Untuk mengubah email, hubungi admin CampusLink.</p>
            </dl>

            @include('partials.field', ['name' => 'nama_organisasi', 'label' => 'Nama organisasi', 'nilai' => $mitra->nama_organisasi])
            @include('partials.field', ['name' => 'name', 'label' => 'Nama penanggung jawab', 'autocomplete' => 'name', 'nilai' => $user->name])
            @include('partials.field', ['name' => 'kontak', 'label' => 'Kontak', 'hint' => 'Telepon atau email yang bisa dihubungi mahasiswa.', 'nilai' => $mitra->kontak])
            @include('partials.field', ['name' => 'website', 'label' => 'Situs web', 'type' => 'url', 'wajib' => false, 'hint' => 'Diawali http:// atau https://', 'nilai' => $mitra->website])
            @include('partials.textarea', ['name' => 'deskripsi', 'label' => 'Tentang organisasi', 'baris' => 4, 'hint' => 'Maksimal 2000 karakter.', 'nilai' => $mitra->deskripsi])

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary px-8 py-3">Simpan perubahan</button>
            </div>
        </form>
        </div></div>
    </section>
@endsection

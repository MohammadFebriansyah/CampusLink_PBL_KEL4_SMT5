@extends('layouts.panel')

@section('title', 'Profil organisasi')

@section('content')
    @php
        [$varian, $teksStatus] = match ($mitra->status_verifikasi) {
            'disetujui' => ['success', 'Terverifikasi'],
            'pending' => ['warning', 'Menunggu verifikasi'],
            default => ['danger', 'Tidak disetujui'],
        };
    @endphp

    <x-admin.page-header title="Profil organisasi" description="Data ini tampil bersama informasi yang Anda tayangkan.">
        <x-slot:actions>
            <x-admin.badge :variant="$varian" dot>{{ $teksStatus }}</x-admin.badge>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.card class="max-w-3xl">
        <form method="POST" action="{{ route('mitra.profil.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <dl class="rounded-lg bg-muted px-4 py-3 text-sm">
                <dt class="text-muted-foreground">Email</dt>
                <dd class="font-medium">{{ $user->email }}</dd>
                <p class="mt-2 text-xs text-muted-foreground">Untuk mengubah email, hubungi admin CampusLink.</p>
            </dl>

            @include('partials.field', ['name' => 'nama_organisasi', 'label' => 'Nama organisasi', 'nilai' => $mitra->nama_organisasi])
            @include('partials.field', ['name' => 'name', 'label' => 'Nama penanggung jawab', 'autocomplete' => 'name', 'nilai' => $user->name])
            @include('partials.field', ['name' => 'kontak', 'label' => 'Kontak', 'hint' => 'Telepon atau email yang bisa dihubungi mahasiswa.', 'nilai' => $mitra->kontak])
            @include('partials.field', ['name' => 'website', 'label' => 'Situs web', 'type' => 'url', 'wajib' => false, 'hint' => 'Diawali http:// atau https://', 'nilai' => $mitra->website])
            @include('partials.textarea', ['name' => 'deskripsi', 'label' => 'Tentang organisasi', 'baris' => 4, 'hint' => 'Maksimal 2000 karakter.', 'nilai' => $mitra->deskripsi])

            <div class="flex justify-end border-t border-border pt-5">
                <x-admin.button type="submit">Simpan perubahan</x-admin.button>
            </div>
        </form>
    </x-admin.card>
@endsection

@extends('layouts.admin')

@section('title', 'Ubah keahlian')

@section('content')
    <x-admin.page-header title="Ubah keahlian" :description="$keahlian->nama"
                         :back="route('admin.keahlian.index')" back-label="Kembali ke daftar keahlian" />

    <x-admin.card class="max-w-xl">
        <form method="POST" action="{{ route('admin.keahlian.update', $keahlian->id) }}" class="space-y-5">
            @csrf
            @method('PUT')

            @include('partials.field', [
                'name' => 'nama',
                'label' => 'Nama keahlian',
                'nilai' => $keahlian->nama,
                'hint' => $keahlian->pengguna_count > 0 ? 'Dipakai ' . $keahlian->pengguna_count . ' pengguna. Nama baru ikut berlaku di profil mereka.' : null,
            ])

            <div class="flex justify-end gap-2 border-t border-border pt-5">
                <x-admin.button variant="ghost" :href="route('admin.keahlian.index')">Batal</x-admin.button>
                <x-admin.button type="submit">Simpan</x-admin.button>
            </div>
        </form>
    </x-admin.card>
@endsection

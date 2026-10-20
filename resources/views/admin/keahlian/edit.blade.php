@extends('layouts.app')

@section('title', 'Ubah keahlian')

@section('content')
    @include('admin._kepala', ['judul' => 'Ubah keahlian', 'deskripsi' => $keahlian->nama, 'kembali' => [route('admin.keahlian.index'), 'Kembali ke daftar keahlian'], 'aktif' => null])

    <section class="bg-[#f7f8fc]">
        <div class="mx-auto max-w-page px-4 py-8 sm:px-6 md:py-10"><div class="max-w-xl">
        <form method="POST" action="{{ route('admin.keahlian.update', $keahlian->id) }}" class="space-y-5 rounded-2xl border border-fog bg-paper-white p-6 sm:p-8">
            @csrf
            @method('PUT')

            @include('partials.field', [
                'name' => 'nama',
                'label' => 'Nama keahlian',
                'nilai' => $keahlian->nama,
                'hint' => $keahlian->pengguna_count > 0 ? 'Dipakai ' . $keahlian->pengguna_count . ' pengguna. Nama baru ikut berlaku di profil mereka.' : null,
            ])

            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.keahlian.index') }}" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-primary px-8 py-3">Simpan</button>
            </div>
        </form>
        </div>
        </div>
    </section>
@endsection

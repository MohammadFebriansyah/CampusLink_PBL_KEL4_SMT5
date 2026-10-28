@extends('layouts.admin')

@section('title', ($item ? 'Ubah ' : 'Tambah ') . mb_strtolower($label))

@section('content')
    <x-admin.page-header :title="($item ? 'Ubah' : 'Tambah') . ' informasi ' . mb_strtolower($label)"
                         :description="$item?->judul"
                         :back="route('admin.informasi.index', $kategori)" :back-label="'Kembali ke daftar ' . mb_strtolower($label)" />

    <x-admin.card class="max-w-3xl">
        <form method="POST"
              action="{{ $item ? route('admin.informasi.update', [$kategori, $item->id]) : route('admin.informasi.store', $kategori) }}"
              class="space-y-5">
            @csrf
            @if ($item)
                @method('PUT')
            @endif

            @include('partials.field', ['name' => 'penyelenggara', 'label' => 'Penyelenggara', 'hint' => 'Nama pihak yang menyelenggarakan, tampil kepada pembaca.', 'nilai' => $item?->penyelenggara])
            @include('partials.form-informasi', ['kategori' => $kategori, 'item' => $item, 'daftarTingkat' => $daftarTingkat, 'admin' => true])

            <div class="flex justify-end gap-2 border-t border-border pt-5">
                <x-admin.button variant="ghost" :href="route('admin.informasi.index', $kategori)">Batal</x-admin.button>
                <x-admin.button type="submit">{{ $item ? 'Simpan perubahan' : 'Tayangkan' }}</x-admin.button>
            </div>
        </form>
    </x-admin.card>
@endsection

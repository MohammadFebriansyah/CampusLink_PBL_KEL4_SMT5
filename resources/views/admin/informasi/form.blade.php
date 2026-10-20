@extends('layouts.app')

@section('title', ($item ? 'Ubah ' : 'Tambah ') . mb_strtolower($label))

@section('content')
    @include('admin._kepala', [
        'judul' => ($item ? 'Ubah' : 'Tambah') . ' informasi ' . mb_strtolower($label),
        'deskripsi' => $item?->judul,
        'kembali' => [route('admin.informasi.index', $kategori), 'Kembali ke daftar ' . mb_strtolower($label)],
        'aktif' => null,
    ])

    <section class="bg-[#f7f8fc]">
        <div class="mx-auto max-w-page px-4 py-8 sm:px-6 md:py-10"><div class="max-w-3xl">
        <form method="POST"
              action="{{ $item ? route('admin.informasi.update', [$kategori, $item->id]) : route('admin.informasi.store', $kategori) }}"
              class="space-y-5 rounded-2xl border border-fog bg-paper-white p-6 sm:p-8">
            @csrf
            @if ($item)
                @method('PUT')
            @endif

            @include('partials.field', ['name' => 'penyelenggara', 'label' => 'Penyelenggara', 'hint' => 'Nama pihak yang menyelenggarakan, tampil kepada pembaca.', 'nilai' => $item?->penyelenggara])
            @include('partials.form-informasi', ['kategori' => $kategori, 'item' => $item, 'daftarTingkat' => $daftarTingkat, 'admin' => true])

            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.informasi.index', $kategori) }}" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-primary px-8 py-3">{{ $item ? 'Simpan perubahan' : 'Tayangkan' }}</button>
            </div>
        </form>
        </div>
        </div>
    </section>
@endsection

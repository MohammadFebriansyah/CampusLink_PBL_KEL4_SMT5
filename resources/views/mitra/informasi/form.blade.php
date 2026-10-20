@extends('layouts.app')

@section('title', ($item ? 'Ubah ' : 'Tambah ') . mb_strtolower($label))

@section('content')
    @include('mitra._kepala', [
        'judul' => ($item ? 'Ubah' : 'Tambah') . ' informasi ' . mb_strtolower($label),
        'deskripsi' => $item?->judul,
        'kembali' => [route('mitra.informasi.index', $kategori), 'Kembali ke daftar ' . mb_strtolower($label)],
        'aktif' => null,
    ])

    <section class="bg-[#f7f8fc]">
        <div class="mx-auto max-w-page px-4 py-8 sm:px-6 md:py-10"><div class="max-w-3xl">
        <form method="POST"
              action="{{ $item ? route('mitra.informasi.update', [$kategori, $item->id]) : route('mitra.informasi.store', $kategori) }}"
              class="space-y-5 rounded-2xl border border-fog bg-paper-white p-6 sm:p-8">
            @csrf
            @if ($item)
                @method('PUT')
            @endif

            <p class="rounded-xl bg-primary-wash p-4 text-sm text-graphite">
                Tampil sebagai penyelenggara:
                <span class="font-medium text-carbon">{{ $item ? $item->penyelenggara : auth()->user()->mitra->nama_organisasi }}</span>.
                @if (! $item) Informasi langsung tayang setelah disimpan. @endif
            </p>

            @include('partials.form-informasi', ['kategori' => $kategori, 'item' => $item, 'daftarTingkat' => $daftarTingkat])

            <div class="flex justify-end gap-2">
                <a href="{{ route('mitra.informasi.index', $kategori) }}" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-primary px-8 py-3">{{ $item ? 'Simpan perubahan' : 'Tayangkan' }}</button>
            </div>
        </form>

        @if ($item)
            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#fecaca] bg-paper-white p-5">
                <div>
                    <p class="font-medium">Hapus informasi ini</p>
                    <p class="text-sm text-graphite">Tindakan ini permanen dan tidak bisa dibatalkan.</p>
                </div>
                @include('mitra.informasi._hapus', ['kategori' => $kategori, 'item' => $item])
            </div>
        @endif
        </div></div>
    </section>
@endsection

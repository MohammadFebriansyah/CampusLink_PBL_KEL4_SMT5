@extends('layouts.panel')

@section('title', ($item ? 'Ubah ' : 'Tambah ') . mb_strtolower($label))

@section('content')
    <x-admin.page-header :title="($item ? 'Ubah' : 'Tambah') . ' informasi ' . mb_strtolower($label)"
                         :description="$item?->judul"
                         :back="route('mitra.informasi.index', $kategori)" :back-label="'Kembali ke daftar ' . mb_strtolower($label)" />

    <div class="flex max-w-3xl flex-col gap-6">
        <x-admin.card>
            <form method="POST"
                  action="{{ $item ? route('mitra.informasi.update', [$kategori, $item->id]) : route('mitra.informasi.store', $kategori) }}"
                  class="space-y-5">
                @csrf
                @if ($item)
                    @method('PUT')
                @endif

                <p class="rounded-lg bg-muted px-4 py-3 text-sm text-muted-foreground">
                    Tampil sebagai penyelenggara:
                    <span class="font-medium text-foreground">{{ $item ? $item->penyelenggara : auth()->user()->mitra->nama_organisasi }}</span>.
                    @if (! $item) Informasi langsung tayang setelah disimpan. @endif
                </p>

                @include('partials.form-informasi', ['kategori' => $kategori, 'item' => $item, 'daftarTingkat' => $daftarTingkat])

                <div class="flex justify-end gap-2 border-t border-border pt-5">
                    <x-admin.button variant="ghost" :href="route('mitra.informasi.index', $kategori)">Batal</x-admin.button>
                    <x-admin.button type="submit">{{ $item ? 'Simpan perubahan' : 'Tayangkan' }}</x-admin.button>
                </div>
            </form>
        </x-admin.card>

        @if ($item)
            <x-admin.card class="border-red-200 dark:border-red-500/30">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-medium">Hapus informasi ini</p>
                        <p class="text-sm text-muted-foreground">Tindakan ini permanen dan tidak bisa dibatalkan.</p>
                    </div>
                    @include('mitra.informasi._hapus', ['kategori' => $kategori, 'item' => $item])
                </div>
            </x-admin.card>
        @endif
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Keahlian')

@section('content')
    @include('admin._kepala', ['judul' => 'Bidang keahlian', 'deskripsi' => 'Daftar yang bisa dipilih mahasiswa dan dosen di profil, dan dipakai sebagai filter direktori.', 'aktif' => 'keahlian'])

    <section class="bg-[#f7f8fc]">
        <div class="mx-auto max-w-page px-4 py-8 sm:px-6 md:py-10"><div class="max-w-4xl">
        <form method="POST" action="{{ route('admin.keahlian.store') }}" class="flex flex-col gap-3 rounded-2xl border border-fog bg-paper-white p-5 sm:flex-row sm:items-start sm:p-6">
            @csrf
            <div class="flex-1">
                @include('partials.field', ['name' => 'nama', 'label' => 'Tambah keahlian', 'hint' => 'Contoh: Desain Produk'])
            </div>
            <button type="submit" class="btn btn-primary px-8 py-3 sm:mt-7">Tambah</button>
        </form>

        <p class="mt-8 text-sm font-semibold text-carbon">{{ count($daftar) }} keahlian</p>
        <div class="relative mt-3 overflow-x-auto rounded-2xl border border-fog bg-paper-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-fog bg-[#fafbfd] text-[11px] font-semibold tracking-wider text-graphite uppercase">
                    <tr>
                        <th scope="col" class="px-5 py-3">Nama</th>
                        <th scope="col" class="px-5 py-3">Dipakai</th>
                        <th scope="col" class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-fog">
                    @forelse ($daftar as $keahlian)
                        <tr>
                            <td class="px-5 py-3">{{ $keahlian->nama }}</td>
                            <td class="px-4 py-3 text-graphite">{{ $keahlian->pengguna_count }} pengguna</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-1">
                                    <a href="{{ route('admin.keahlian.edit', $keahlian->id) }}" class="px-3 py-1.5 font-medium hover:underline">Ubah</a>
                                    @include('partials.aksi', [
                                        'url' => route('admin.keahlian.destroy', $keahlian->id),
                                        'method' => 'DELETE',
                                        'tombol' => 'Hapus',
                                        'pertanyaan' => $keahlian->pengguna_count > 0 ? 'Dilepas dari ' . $keahlian->pengguna_count . ' profil. Hapus?' : 'Hapus?',
                                        'ya' => 'Ya, hapus',
                                    ])
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-14 text-center font-semibold">Belum ada keahlian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
        </div>
    </section>
@endsection

@extends('layouts.panel')

@section('title', 'Kelola ' . mb_strtolower($label))

@section('content')
    @include('admin._kepala', [
        'judul' => 'Informasi ' . mb_strtolower($label),
        'deskripsi' => 'Semua informasi ' . mb_strtolower($label) . ' dari mitra dan admin, termasuk yang ditutup dan disembunyikan.',
        'aksi' => '<a href="' . e(route('admin.informasi.create', $kategori)) . '" class="btn btn-primary px-6 py-3">+ Tambah ' . e(mb_strtolower($label)) . '</a>',
    ])

    <section class="bg-[#f7f8fc]">
        <div class="mx-auto max-w-page px-4 py-8 sm:px-6 md:py-10">
        <form method="GET" action="{{ route('admin.informasi.index', $kategori) }}" role="search"
              class="flex flex-col gap-2 rounded-3xl bg-paper-white p-2 ring-1 ring-fog sm:flex-row sm:items-center sm:rounded-full">
            <div class="flex flex-1 items-center gap-2 px-3">
                <svg class="size-5 shrink-0 text-ash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <label for="q" class="sr-only">Judul atau penyelenggara</label>
                <input id="q" name="q" type="search" maxlength="100" value="{{ $filter['q'] ?? '' }}" placeholder="Cari judul atau penyelenggara" class="w-full bg-transparent py-2.5 placeholder:text-ash focus:outline-none">
            </div>
            <div>
                <label for="tayang" class="sr-only">Status tayang</label>
                <select id="tayang" name="tayang" class="w-full rounded-full bg-mist px-4 py-2.5 text-sm font-medium">
                    <option value="">Semua status</option>
                    @foreach (['dibuka' => 'Dibuka', 'ditutup' => 'Ditutup', 'disembunyikan' => 'Disembunyikan'] as $nilai => $nama)
                        <option value="{{ $nilai }}" @selected(($filter['tayang'] ?? null) === $nilai)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary px-7 py-2.5">Cari</button>
        </form>

        @if ($errors->any())
            <p class="mt-3 text-sm text-ember" role="alert">{{ $errors->first() }}</p>
        @endif

        <p class="mt-6 text-sm font-semibold text-carbon" aria-live="polite">{{ $items->total() }} informasi</p>

        <div class="relative mt-3 overflow-x-auto rounded-2xl border border-fog bg-paper-white">
            <table class="w-full min-w-[48rem] text-left text-sm">
                <thead class="border-b border-fog bg-[#fafbfd] text-[11px] font-semibold tracking-wider text-graphite uppercase">
                    <tr>
                        <th scope="col" class="px-5 py-3">Judul</th>
                        <th scope="col" class="px-5 py-3">Batas</th>
                        <th scope="col" class="px-5 py-3">Status</th>
                        <th scope="col" class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-fog">
                    @forelse ($items as $item)
                        @php
                            $status = \App\Support\StatusInformasi::dari($item);
                        @endphp
                        <tr class="transition hover:bg-[#fafbfd]">
                            <td class="px-5 py-3">
                                <p class="font-semibold text-carbon">{{ $item->judul }}</p>
                                <p class="text-graphite">{{ $item->penyelenggara }}, ditambahkan oleh {{ $item->user->name }}</p>
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    @if (! $item->lewatCampuslink())
                                        <span class="rounded-full border border-fog px-2 py-0.5 text-[11px] font-medium text-graphite">Kegiatan luar · tautan penyelenggara</span>
                                    @elseif ($item->user->role === 'admin')
                                        <span class="rounded-full bg-primary-wash px-2 py-0.5 text-[11px] font-semibold text-primary">Kegiatan kampus · {{ $item->pendaftaran_count }} pendaftar</span>
                                    @else
                                        <span class="rounded-full bg-[#f3e8ff] px-2 py-0.5 text-[11px] font-semibold text-[#7e22ce]">Mitra · {{ $item->pendaftaran_count }} pendaftar</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap text-graphite">{{ \App\Support\Tanggal::panjang($item->batas_pendaftaran) }}</td>
                            <td class="px-5 py-3">
                                @php
                                    $warnaStatus = match ($status) {
                                        'dibuka' => ['bg-mint-wash text-[#15803d]', 'bg-[#16a34a]'],
                                        'disembunyikan' => ['bg-accent-wash text-accent-dark', 'bg-accent-dark'],
                                        default => ['bg-mist text-graphite', 'bg-[#94a3b8]'],
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $warnaStatus[0] }}">
                                    <span class="size-1.5 rounded-full {{ $warnaStatus[1] }}" aria-hidden="true"></span>{{ \App\Support\StatusInformasi::label($status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                    @if ($status !== 'disembunyikan')
                                        <a href="{{ route($kategori . '.show', $item) }}" class="rounded-full px-3 py-1.5 font-medium text-graphite hover:bg-mist hover:text-carbon">Lihat</a>
                                    @endif
                                    <a href="{{ route('admin.informasi.edit', [$kategori, $item->id]) }}" class="rounded-full px-3 py-1.5 font-medium text-graphite hover:bg-mist hover:text-carbon">Ubah</a>
                                    @if ($status === 'disembunyikan')
                                        @include('partials.aksi', ['url' => route('admin.informasi.tampilkan', [$kategori, $item->id]), 'tombol' => 'Tampilkan'])
                                    @else
                                        @include('partials.aksi', ['url' => route('admin.informasi.sembunyikan', [$kategori, $item->id]), 'tombol' => 'Sembunyikan', 'pertanyaan' => 'Sembunyikan?', 'ya' => 'Ya'])
                                    @endif
                                    @include('partials.aksi', ['url' => route('admin.informasi.destroy', [$kategori, $item->id]), 'method' => 'DELETE', 'tombol' => 'Hapus', 'pertanyaan' => 'Hapus permanen?', 'ya' => 'Ya, hapus'])
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-14 text-center">
                                <p class="font-semibold">{{ ! empty($filter) ? 'Tidak ada informasi yang cocok.' : 'Belum ada informasi ' . mb_strtolower($label) . '.' }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.pager', ['paginator' => $items])
        </div>
    </section>
@endsection

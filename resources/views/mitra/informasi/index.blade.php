@extends('layouts.app')

@section('title', 'Kelola ' . mb_strtolower($label))

@section('content')
    @include('mitra._kepala', [
        'judul' => 'Informasi ' . mb_strtolower($label) . ' saya',
        'deskripsi' => 'Semua informasi ' . mb_strtolower($label) . ' yang Anda tayangkan, termasuk yang sudah ditutup.',
        'aktif' => $kategori,
        'aksi' => '<a href="' . e(route('mitra.informasi.create', $kategori)) . '" class="btn btn-primary px-6 py-3">+ Tambah ' . e(mb_strtolower($label)) . '</a>',
    ])

    <section class="bg-[#f7f8fc]">
        <div class="mx-auto max-w-page px-4 py-8 sm:px-6 md:py-10">
            <p class="text-sm font-semibold text-carbon">{{ $items->total() }} informasi</p>

            <div class="mt-3 grid gap-3 lg:grid-cols-2">
                @forelse ($items as $item)
                    @php
                        $status = \App\Support\StatusInformasi::dari($item);
                        $warnaStatus = match ($status) {
                            'dibuka' => ['bg-mint-wash text-[#15803d]', 'bg-[#16a34a]', 'border-l-[#16a34a]'],
                            'disembunyikan' => ['bg-accent-wash text-accent-dark', 'bg-accent-dark', 'border-l-accent-dark'],
                            default => ['bg-mist text-graphite', 'bg-[#94a3b8]', 'border-l-[#94a3b8]'],
                        };
                        $sisa = \App\Support\Tanggal::sisaHari($item->batas_pendaftaran);
                    @endphp
                    <article class="flex flex-col rounded-2xl border border-l-4 border-fog bg-paper-white p-5 {{ $warnaStatus[2] }}">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="font-semibold text-carbon">{{ $item->judul }}</h2>
                                <p class="mt-1 flex items-center gap-1.5 text-sm text-graphite">
                                    <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                                    Batas pendaftaran {{ \App\Support\Tanggal::panjang($item->batas_pendaftaran) }}
                                    @if ($status === 'dibuka')
                                        <span class="text-accent-dark">· {{ \App\Support\Tanggal::labelSisa($item->batas_pendaftaran) }}</span>
                                    @endif
                                </p>
                            </div>
                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $warnaStatus[0] }}">
                                <span class="size-1.5 rounded-full {{ $warnaStatus[1] }}" aria-hidden="true"></span>{{ \App\Support\StatusInformasi::label($status) }}
                            </span>
                        </div>

                        <a href="{{ route('mitra.pendaftar.index') }}" class="mt-3 inline-flex w-fit items-center gap-2 rounded-xl bg-[#f7f8fc] px-3 py-2 text-sm hover:bg-primary-wash">
                            <span class="font-semibold text-carbon">{{ $item->pendaftaran_count }} pendaftar</span>
                            @if ($item->menunggu_count > 0)
                                <span class="rounded-full bg-accent px-2 py-0.5 text-[11px] font-bold text-navy">{{ $item->menunggu_count }} menunggu</span>
                            @endif
                        </a>

                        @if ($status === 'disembunyikan')
                            <p class="mt-3 rounded-xl bg-accent-wash px-3 py-2 text-sm text-carbon">Informasi ini tidak tampil untuk pembaca. Hubungi admin CampusLink bila menurut Anda ini keliru.</p>
                        @endif

                        <div class="mt-auto flex flex-wrap items-center gap-2 pt-4">
                            @if ($status !== 'disembunyikan')
                                <a href="{{ route($kategori . '.show', $item) }}" class="inline-flex items-center rounded-full border border-fog px-3.5 py-1.5 text-sm font-medium transition hover:border-accent/50 hover:text-accent-dark">Lihat</a>
                            @endif
                            <a href="{{ route('mitra.informasi.edit', [$kategori, $item->id]) }}" class="inline-flex items-center rounded-full border border-fog px-3.5 py-1.5 text-sm font-medium transition hover:border-accent/50 hover:text-accent-dark">Ubah</a>
                            <div class="ml-auto">
                                @include('mitra.informasi._hapus', ['kategori' => $kategori, 'item' => $item])
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-fog bg-paper-white px-6 py-14 text-center lg:col-span-2">
                        <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-accent-wash text-2xl font-bold text-accent-dark">+</span>
                        <p class="mt-4 text-subheading font-semibold">Belum ada informasi {{ mb_strtolower($label) }}.</p>
                        <p class="mt-1 text-graphite">Informasi yang Anda tambahkan langsung tayang untuk mahasiswa dan dosen.</p>
                        <a href="{{ route('mitra.informasi.create', $kategori) }}" class="btn btn-primary mt-5 px-6 py-3">Tambah {{ mb_strtolower($label) }} pertama</a>
                    </div>
                @endforelse
            </div>

            @include('partials.pager', ['paginator' => $items])
        </div>
    </section>
@endsection

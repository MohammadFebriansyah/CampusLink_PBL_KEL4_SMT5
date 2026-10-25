@extends('layouts.panel')

@section('title', 'Verifikasi mitra')

@section('content')
    @include('admin._kepala', ['judul' => 'Verifikasi mitra', 'deskripsi' => 'Mitra baru bisa masuk dan menayangkan informasi setelah disetujui.'])

    <section class="bg-[#f7f8fc]">
        <div class="mx-auto max-w-page px-4 py-8 sm:px-6 md:py-10">
            {{-- Pilihan status --}}
            <nav class="inline-flex flex-wrap gap-1 rounded-full bg-paper-white p-1 ring-1 ring-fog" aria-label="Status verifikasi">
                @foreach (['pending' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'] as $kunci => $nama)
                    @php $aktifTab = $status === $kunci; @endphp
                    <a href="{{ route('admin.mitra.index', ['verifikasi' => $kunci]) }}"
                       class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition {{ $aktifTab ? 'bg-navy text-paper-white' : 'text-graphite hover:bg-mist hover:text-carbon' }}"
                       @if ($aktifTab) aria-current="page" @endif>
                        {{ $nama }}
                        <span class="rounded-full px-2 py-0.5 text-[11px] {{ $aktifTab ? 'bg-accent text-navy' : 'bg-mist text-graphite' }}">{{ $jumlah[$kunci] ?? 0 }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="mt-6 grid gap-4 lg:grid-cols-2">
                @forelse ($daftar as $mitra)
                    @php
                        $inisial = mb_strtoupper(collect(preg_split('/\s+/', trim(preg_replace('/^(PT|CV|UD)\.?\s+/i', '', $mitra->nama_organisasi))))->filter()->take(2)->map(fn ($k) => mb_substr($k, 0, 1))->join(''));
                    @endphp
                    <article class="flex flex-col rounded-2xl border border-fog bg-paper-white p-5 sm:p-6">
                        <div class="flex items-start gap-4">
                            <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-navy text-sm font-bold text-accent" aria-hidden="true">{{ $inisial ?: '?' }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <h2 class="font-semibold text-carbon">{{ $mitra->nama_organisasi }}</h2>
                                    <span class="rounded-full bg-mist px-2.5 py-0.5 text-[11px] font-semibold text-graphite">Mendaftar {{ \App\Support\Tanggal::panjang($mitra->created_at) }}</span>
                                </div>
                                <p class="mt-0.5 text-sm break-words text-graphite">{{ $mitra->user->name }}, {{ $mitra->user->email }}</p>
                            </div>
                        </div>

                        <dl class="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                            <div class="rounded-xl bg-[#f7f8fc] px-3 py-2.5"><dt class="text-caption text-graphite">Kontak</dt><dd class="font-semibold break-words">{{ $mitra->kontak }}</dd></div>
                            <div class="rounded-xl bg-[#f7f8fc] px-3 py-2.5"><dt class="text-caption text-graphite">Situs web</dt><dd class="font-semibold break-words">{{ $mitra->website ?: '-' }}</dd></div>
                            @if ($mitra->deskripsi)
                                <div class="rounded-xl bg-[#f7f8fc] px-3 py-2.5 sm:col-span-2"><dt class="text-caption text-graphite">Tentang organisasi</dt><dd class="text-carbon">{{ $mitra->deskripsi }}</dd></div>
                            @endif
                        </dl>

                        <div class="mt-auto flex flex-wrap items-center gap-2 pt-5">
                            @if ($status !== 'disetujui')
                                @include('partials.aksi', ['url' => route('admin.mitra.setujui', $mitra->id), 'tombol' => 'Setujui', 'gaya' => 'utama'])
                            @endif
                            @if ($status !== 'ditolak')
                                @include('partials.aksi', ['url' => route('admin.mitra.tolak', $mitra->id), 'tombol' => 'Tolak', 'pertanyaan' => 'Tolak mitra ini?', 'ya' => 'Ya, tolak'])
                            @endif
                            <a href="{{ route('admin.pengguna.show', $mitra->user->id) }}" class="ml-auto text-sm font-medium text-accent-dark hover:underline">Lihat akun &rarr;</a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-fog bg-paper-white px-6 py-14 text-center lg:col-span-2">
                        <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-mint-wash text-[#15803d]">
                            <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                        </span>
                        <p class="mt-4 font-semibold">{{ $status === 'pending' ? 'Tidak ada mitra yang menunggu verifikasi.' : 'Belum ada mitra di daftar ini.' }}</p>
                    </div>
                @endforelse
            </div>

            @include('partials.pager', ['paginator' => $daftar])
        </div>
    </section>
@endsection

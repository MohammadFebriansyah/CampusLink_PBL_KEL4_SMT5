@extends('layouts.app')

@section('title', 'Kegiatan saya')

@section('content')
    @php
        $gaya = [
            'menunggu' => ['bg-accent-wash text-accent-dark', 'bg-accent-dark', 'Menunggu'],
            'diterima' => ['bg-mint-wash text-[#15803d]', 'bg-[#16a34a]', 'Diterima'],
            'ditolak' => ['bg-[#fee2e2] text-[#b91c1c]', 'bg-[#dc2626]', 'Ditolak'],
        ];
        $label = \App\Support\Informasi::LABEL;
        $kunciAwal = $detail ? $detail['kategori'] . '-' . $detail['item']->id : null;
        // Kelas kartu yang bisa diklik; bingkai oranye untuk kartu yang sedang dibuka di panel.
        $kartu = 'group block rounded-2xl border border-fog bg-paper-white p-4 transition hover:-translate-y-0.5 hover:border-accent/40 hover:shadow-[0_18px_40px_-24px_rgb(15_30_61/0.45)]';
        $aktifKelas = 'lg:border-accent lg:ring-2 lg:ring-accent/30';
    @endphp

    <section class="relative isolate overflow-hidden bg-navy text-paper-white">
        <div class="hero-dots pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -top-32 right-[-5%] size-[420px] rounded-full bg-primary/50 blur-3xl" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-page px-4 py-10 sm:px-6">
            <p class="text-sm font-semibold text-accent">Mahasiswa</p>
            <h1 class="mt-2 text-heading-sm font-bold tracking-[-0.5px] sm:text-heading">Kegiatan saya</h1>
            <p class="mt-1 text-white/70">Status pendaftaran magang, lomba, dan beasiswa, serta permintaan bimbingan ke dosen. Klik kegiatan untuk melihat detailnya.</p>
            <div class="mt-6 flex flex-wrap gap-2 text-sm">
                @foreach (['menunggu', 'diterima', 'ditolak'] as $s)
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 ring-1 ring-white/15">
                        <span class="size-2 rounded-full {{ $gaya[$s][1] }}" aria-hidden="true"></span>
                        {{ $gaya[$s][2] }} <span class="font-bold">{{ $pendaftaran->where('status', $s)->count() }}</span>
                    </span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Daftar (kiri) + panel detail (kanan, layar lebar). Di HP, kartu membuka halaman detail. --}}
    <section class="bg-[#f7f8fc]">
        <div class="mx-auto grid max-w-page grid-cols-1 gap-6 px-4 py-8 sm:px-6 md:py-10 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:items-start"
             x-data="panelDetail(@js($kunciAwal))">
            <div class="min-w-0 space-y-8">
                {{-- Pendaftaran --}}
                <div>
                    <h2 class="text-subheading font-semibold">Pendaftaran <span class="text-graphite">({{ $pendaftaran->count() }})</span></h2>
                    <div class="mt-3 space-y-2.5">
                        @forelse ($pendaftaran as $p)
                            @php
                                $info = $p->informasi;
                                $kunci = $p->informasi_type . '-' . $p->informasi_id;
                                $bisa = in_array($kunci, $bisaDibuka, true);
                            @endphp
                            @if ($bisa)
                                <a href="{{ route($p->informasi_type . '.show', $info) }}"
                                   @click="buka($event, @js($kunci))"
                                   :class="aktif === @js($kunci) ? @js($aktifKelas) : ''"
                                   :aria-current="aktif === @js($kunci) ? 'true' : null"
                                   class="{{ $kartu }}">
                            @else
                                <div class="{{ $kartu }} pointer-events-none opacity-70">
                            @endif
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-bold tracking-wider text-accent-dark uppercase">{{ $label[$p->informasi_type] ?? '' }}</p>
                                        <p class="mt-0.5 font-semibold text-carbon group-hover:text-accent-dark">{{ $info?->judul ?? 'Informasi sudah dihapus' }}</p>
                                        @if ($info)
                                            <p class="truncate text-caption text-graphite">{{ $info->penyelenggara }}</p>
                                        @endif
                                    </div>
                                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $gaya[$p->status][0] }}">
                                        <span class="size-1.5 rounded-full {{ $gaya[$p->status][1] }}" aria-hidden="true"></span>{{ $gaya[$p->status][2] }}
                                    </span>
                                </div>
                                <p class="mt-2 text-caption text-graphite">
                                    Dikirim {{ \App\Support\Tanggal::panjang($p->created_at) }}@if ($p->ditinjau_pada) · ditinjau {{ \App\Support\Tanggal::panjang($p->ditinjau_pada) }}@endif
                                    @if ($info && ! $bisa) · disembunyikan admin @endif
                                </p>
                                @if ($p->catatan)
                                    <p class="mt-2 line-clamp-2 rounded-lg bg-[#f7f8fc] px-2.5 py-1.5 text-sm"><span class="font-semibold">Catatan:</span> {{ $p->catatan }}</p>
                                @endif
                            @if ($bisa)
                                </a>
                            @else
                                </div>
                            @endif
                        @empty
                            <div class="rounded-3xl border border-dashed border-fog bg-paper-white px-6 py-10 text-center">
                                <p class="font-semibold">Belum ada pendaftaran.</p>
                                <p class="mt-1 text-sm text-graphite">Cari kegiatan yang bertanda "Daftar di CampusLink", lalu unggah CV Anda.</p>
                                <div class="mt-4 flex flex-wrap justify-center gap-2">
                                    <a href="{{ route('magang.index') }}" class="btn btn-primary px-5 py-2.5">Lihat magang</a>
                                    <a href="{{ route('lomba.index') }}" class="btn btn-outline px-5 py-2.5">Lomba</a>
                                    <a href="{{ route('beasiswa.index') }}" class="btn btn-outline px-5 py-2.5">Beasiswa</a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Bimbingan --}}
                <div>
                    <h2 class="text-subheading font-semibold">Bimbingan dosen <span class="text-graphite">({{ $bimbingan->count() }})</span></h2>
                    <div class="mt-3 space-y-2.5">
                        @forelse ($bimbingan as $b)
                            @php
                                $info = $b->informasi;
                                $kunci = $b->informasi_type . '-' . $b->informasi_id;
                                $bisa = in_array($kunci, $bisaDibuka, true);
                            @endphp
                            @if ($bisa)
                                <a href="{{ route($b->informasi_type . '.show', $info) }}"
                                   @click="buka($event, @js($kunci))"
                                   :class="aktif === @js($kunci) ? @js($aktifKelas) : ''"
                                   class="{{ $kartu }}">
                            @else
                                <div class="{{ $kartu }} pointer-events-none opacity-70">
                            @endif
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-carbon group-hover:text-accent-dark">{{ $b->dosen?->name }}</p>
                                        <p class="truncate text-caption text-graphite">{{ $label[$b->informasi_type] ?? '' }}: {{ $info?->judul ?? 'Informasi sudah dihapus' }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $gaya[$b->status][0] }}">{{ $gaya[$b->status][2] }}</span>
                                </div>
                                @if ($b->catatan)
                                    <p class="mt-2 line-clamp-2 rounded-lg bg-[#f7f8fc] px-2.5 py-1.5 text-sm"><span class="font-semibold">Catatan dosen:</span> {{ $b->catatan }}</p>
                                @endif
                            @if ($bisa)
                                </a>
                            @else
                                </div>
                            @endif
                        @empty
                            <div class="rounded-3xl border border-dashed border-fog bg-paper-white px-6 py-8 text-center">
                                <p class="font-semibold">Belum ada permintaan bimbingan.</p>
                                <p class="mt-1 text-sm text-graphite">Setelah Anda diterima di magang atau lomba, pilih dosen pembimbing di detail kegiatannya.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Panel detail: status, CV, batal, dan dosen pembimbing tampil di sini tanpa pindah halaman --}}
            @if ($detail)
                <aside class="hidden lg:sticky lg:top-24 lg:block" aria-label="Detail kegiatan" aria-live="polite">
                    <div x-ref="panel" class="relative max-h-[calc(100svh-7rem)] overflow-y-auto overscroll-contain rounded-3xl border border-fog bg-paper-white p-7 shadow-[0_24px_60px_-36px_rgb(15_30_61/0.5)]">
                        <div x-show="memuat" x-cloak x-transition.opacity class="absolute inset-0 z-10 flex items-start justify-center bg-paper-white/70 pt-24 backdrop-blur-[1px]">
                            <span class="size-8 animate-spin rounded-full border-[3px] border-accent/30 border-t-accent" role="status" aria-label="Memuat"></span>
                        </div>
                        <div x-ref="isi">
                            @include('informasi._detail', $detail + ['panel' => true])
                        </div>
                    </div>
                </aside>
            @endif
        </div>
    </section>
@endsection

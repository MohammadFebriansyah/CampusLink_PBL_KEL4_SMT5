@extends('layouts.app')

@section('title', 'Dasbor admin')

@section('content')
    @php
        $ikonPeran = [
            'mahasiswa' => '<path d="m12 4 10 5-10 5L2 9l10-5Z"/><path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/>',
            'dosen' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
            'mitra' => '<path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6"/>',
        ];
        $ikonInfo = [
            'magang' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 13h18"/>',
            'lomba' => '<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4Z"/><path d="M17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/>',
            'beasiswa' => '<path d="m12 4 10 5-10 5L2 9l10-5Z"/><path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5M22 9v6"/>',
        ];
        // Status informasi: warna + label + angka (warna tidak pernah berdiri sendiri).
        $statusInfo = [
            'dibuka' => ['Dibuka', 'bg-[#16a34a]'],
            'ditutup' => ['Ditutup', 'bg-[#94a3b8]'],
            'disembunyikan' => ['Disembunyikan', 'bg-accent-dark'],
        ];
        $totalPengguna = collect($pengguna)->sum('total');
        $totalNonaktif = collect($pengguna)->sum('nonaktif');
        $totalInfo = collect($informasi)->sum('total');
        $totalDibuka = collect($informasi)->sum('dibuka');
    @endphp

    {{-- Kepala dasbor --}}
    <section class="relative isolate overflow-hidden bg-navy text-paper-white">
        <div class="hero-dots pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -top-32 right-[-5%] size-[420px] rounded-full bg-primary/50 blur-3xl" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-page px-4 pt-10 pb-10 sm:px-6 md:pt-12">
            <p class="text-sm font-semibold text-accent">Panel admin · {{ \App\Support\Tanggal::panjang(today()) }}</p>
            <h1 class="mt-2 text-heading font-bold tracking-[-0.8px]">Halo, {{ auth()->user()->name }}</h1>
            <p class="mt-2 text-white/70">Ringkasan pengguna dan informasi CampusLink hari ini.</p>
            @include('admin._menu', ['aktif' => 'dashboard', 'gelap' => true])
        </div>
    </section>

    <section class="bg-[#f7f8fc]">
        <div class="mx-auto max-w-page space-y-10 px-4 py-10 sm:px-6 md:py-12">

            {{-- Perlu tindakan --}}
            @if ($menunggu > 0)
                <a href="{{ route('admin.mitra.index') }}"
                   class="group flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-accent/40 bg-accent-wash p-5 transition hover:border-accent sm:p-6">
                    <div class="flex items-center gap-4">
                        <span class="relative flex size-12 shrink-0 items-center justify-center rounded-xl bg-accent text-navy">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6"/></svg>
                            <span class="absolute -top-1 -right-1 flex size-3"><span class="absolute inline-flex size-full animate-ping rounded-full bg-accent-dark opacity-60"></span><span class="relative inline-flex size-3 rounded-full bg-accent-dark"></span></span>
                        </span>
                        <div>
                            <p class="font-semibold text-carbon">{{ $menunggu }} akun mitra menunggu verifikasi</p>
                            <p class="text-sm text-graphite">Mitra belum bisa masuk sebelum disetujui.</p>
                        </div>
                    </div>
                    <span class="btn btn-primary px-6 py-2.5">Tinjau sekarang &rarr;</span>
                </a>
            @else
                <div class="flex items-center gap-3 rounded-2xl border border-[#16a34a]/25 bg-mint-wash px-5 py-4 text-sm">
                    <svg class="size-5 shrink-0 text-[#15803d]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                    <span><span class="font-semibold">Tidak ada yang perlu ditinjau.</span> Semua pendaftaran mitra sudah diproses.</span>
                </div>
            @endif

            @if ($pendaftarMenunggu > 0)
                <a href="{{ route('admin.pendaftar.index') }}" class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-primary/20 bg-primary-wash px-5 py-4 transition hover:border-primary/40">
                    <span><span class="font-semibold">{{ $pendaftarMenunggu }} pendaftar kegiatan kampus</span> menunggu ditinjau admin.</span>
                    <span class="font-semibold text-primary">Tinjau &rarr;</span>
                </a>
            @endif

            {{-- Pengguna --}}
            <div>
                <div class="flex items-end justify-between gap-3">
                    <h2 class="text-subheading font-semibold">Pengguna</h2>
                    <a href="{{ route('admin.pengguna.index') }}" class="text-sm font-medium text-accent-dark hover:underline">Kelola pengguna &rarr;</a>
                </div>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-2xl bg-navy p-5 text-paper-white">
                        <p class="text-sm text-white/70">Total pengguna</p>
                        <p class="mt-2 text-heading font-bold tabular-nums" x-data="hitung({{ $totalPengguna }})" x-text="n">{{ $totalPengguna }}</p>
                        <p class="mt-1 text-sm text-white/60">{{ $totalNonaktif }} nonaktif</p>
                    </div>
                    @foreach ($pengguna as $peran => $data)
                        <div class="rounded-2xl border border-fog bg-paper-white p-5">
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-graphite">{{ $data['label'] }}</p>
                                <span class="flex size-9 items-center justify-center rounded-xl bg-accent-wash text-accent-dark">
                                    <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $ikonPeran[$peran] !!}</svg>
                                </span>
                            </div>
                            <p class="mt-1 text-heading font-bold tabular-nums text-carbon" x-data="hitung({{ $data['total'] }})" x-text="n">{{ $data['total'] }}</p>
                            <p class="mt-1 text-sm {{ $data['nonaktif'] > 0 ? 'text-accent-dark' : 'text-graphite' }}">{{ $data['nonaktif'] }} nonaktif</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Informasi --}}
            <div>
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h2 class="text-subheading font-semibold">Informasi</h2>
                    <p class="text-sm text-graphite"><span class="font-semibold text-carbon">{{ $totalDibuka }}</span> dari {{ $totalInfo }} informasi sedang dibuka</p>
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    @foreach ($informasi as $kunci => $data)
                        <div class="flex flex-col rounded-2xl border border-fog bg-paper-white p-5">
                            <div class="flex items-center gap-3">
                                <span class="flex size-10 items-center justify-center rounded-xl bg-navy text-accent">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $ikonInfo[$kunci] !!}</svg>
                                </span>
                                <p class="flex-1 font-semibold">{{ $data['label'] }}</p>
                                <p class="text-heading-sm font-bold tabular-nums">{{ $data['total'] }}</p>
                            </div>

                            {{-- Batang proporsi status; arahkan kursor ke tiap bagian untuk melihat angkanya --}}
                            <div class="mt-5 flex h-2.5 gap-0.5 overflow-hidden rounded-full bg-mist" role="img"
                                 aria-label="{{ $data['label'] }}: {{ $data['dibuka'] }} dibuka, {{ $data['ditutup'] }} ditutup, {{ $data['disembunyikan'] }} disembunyikan">
                                @foreach ($statusInfo as $status => [$nama, $warna])
                                    @if ($data[$status] > 0)
                                        <span class="{{ $warna }} h-full first:rounded-l-full last:rounded-r-full" style="width: {{ $data[$status] / max($data['total'], 1) * 100 }}%"
                                              title="{{ $nama }}: {{ $data[$status] }}"></span>
                                    @endif
                                @endforeach
                            </div>

                            <dl class="mt-4 space-y-2 text-sm">
                                @foreach ($statusInfo as $status => [$nama, $warna])
                                    <div class="flex items-center justify-between">
                                        <dt class="flex items-center gap-2 text-graphite"><span class="size-2.5 rounded-full {{ $warna }}" aria-hidden="true"></span>{{ $nama }}</dt>
                                        <dd class="font-semibold tabular-nums text-carbon">{{ $data[$status] }}</dd>
                                    </div>
                                @endforeach
                            </dl>

                            <a href="{{ route('admin.informasi.index', $kunci) }}" class="btn btn-outline mt-5 w-full py-2.5">Kelola {{ mb_strtolower($data['label']) }}</a>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Pintasan --}}
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['Verifikasi mitra', 'Setujui atau tolak pendaftaran mitra baru.', route('admin.mitra.index'), '<path d="M12 3 4 6v6c0 4.5 3.4 8.2 8 9 4.6-.8 8-4.5 8-9V6l-8-3Z"/><path d="m9 12 2 2 4-4"/>'],
                    ['Pengguna', 'Cari, ubah, atau nonaktifkan akun.', route('admin.pengguna.index'), '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5a6.5 6.5 0 0 1 3.5 5.5"/>'],
                    ['Keahlian', 'Atur daftar bidang keahlian untuk direktori.', route('admin.keahlian.index'), '<path d="m12 3 2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.4 6.8 19.1l1-5.8L3.5 9.2l5.9-.9L12 3Z"/>'],
                ] as [$judul, $teks, $url, $ikon])
                    <a href="{{ $url }}" class="group flex items-start gap-4 rounded-2xl border border-fog bg-paper-white p-5 transition hover:-translate-y-0.5 hover:border-accent/40 hover:shadow-[0_18px_40px_-24px_rgb(15_30_61/0.45)]">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-accent-wash text-accent-dark">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $ikon !!}</svg>
                        </span>
                        <span>
                            <span class="block font-semibold group-hover:text-accent-dark">{{ $judul }}</span>
                            <span class="mt-0.5 block text-sm text-graphite">{{ $teks }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endsection

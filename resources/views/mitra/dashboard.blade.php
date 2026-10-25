@extends('layouts.panel')

@section('title', 'Dashboard mitra')

@section('content')
    @php
        $ikonInfo = [
            'magang' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 13h18"/>',
            'lomba' => '<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4Z"/><path d="M17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/>',
            'beasiswa' => '<path d="m12 4 10 5-10 5L2 9l10-5Z"/><path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5M22 9v6"/>',
        ];
        // Status: warna + label + angka (warna tidak pernah berdiri sendiri).
        $statusInfo = [
            'dibuka' => ['Dibuka', 'bg-[#16a34a]'],
            'ditutup' => ['Ditutup', 'bg-[#94a3b8]'],
            'disembunyikan' => ['Disembunyikan admin', 'bg-accent-dark'],
        ];
        $total = collect($ringkasan)->sum('total');
        $dibuka = collect($ringkasan)->sum('dibuka');
        $disembunyikan = collect($ringkasan)->sum('disembunyikan');
        $profilLengkap = $mitra->website && $mitra->deskripsi;
    @endphp

    @include('mitra._kepala', [
        'atas' => 'Panel mitra · ' . \App\Support\Tanggal::panjang(today()),
        'judul' => 'Dashboard ' . $mitra->nama_organisasi,
        'deskripsi' => 'Kelola informasi yang Anda tayangkan untuk mahasiswa dan dosen Polinema.',
    ])

    <section class="bg-[#f7f8fc]">
        <div class="mx-auto max-w-page space-y-8 px-4 py-8 sm:px-6 md:py-10">

            {{-- Pemberitahuan --}}
            @if ($disembunyikan > 0)
                <div class="flex items-start gap-3 rounded-2xl border border-accent/40 bg-accent-wash px-5 py-4 text-sm">
                    <svg class="mt-0.5 size-5 shrink-0 text-accent-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
                    <p><span class="font-semibold">{{ $disembunyikan }} informasi disembunyikan admin</span> dan tidak tampil untuk pembaca. Hubungi admin CampusLink bila menurut Anda ini keliru.</p>
                </div>
            @elseif (! $profilLengkap)
                <a href="{{ route('mitra.profil.show') }}" class="flex items-center justify-between gap-4 rounded-2xl border border-primary/15 bg-primary-wash px-5 py-4 text-sm transition hover:border-primary/30">
                    <span><span class="font-semibold">Lengkapi profil organisasi.</span> Situs web dan deskripsi membantu mahasiswa mengenal organisasi Anda.</span>
                    <span class="shrink-0 font-semibold text-primary">Lengkapi &rarr;</span>
                </a>
            @endif

            {{-- Pendaftar yang perlu ditinjau --}}
            @if ($pendaftarMenunggu > 0)
                <a href="{{ route('mitra.pendaftar.index') }}" class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-accent/40 bg-accent-wash p-5 transition hover:border-accent sm:p-6">
                    <div class="flex items-center gap-4">
                        <span class="relative flex size-12 shrink-0 items-center justify-center rounded-xl bg-accent text-navy">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5a6.5 6.5 0 0 1 3.5 5.5"/></svg>
                            <span class="absolute -top-1 -right-1 flex size-3"><span class="absolute inline-flex size-full animate-ping rounded-full bg-accent-dark opacity-60"></span><span class="relative inline-flex size-3 rounded-full bg-accent-dark"></span></span>
                        </span>
                        <div>
                            <p class="font-semibold text-carbon">{{ $pendaftarMenunggu }} pendaftar menunggu ditinjau</p>
                            <p class="text-sm text-graphite">Lihat profil dan CV mereka, lalu terima atau tolak.</p>
                        </div>
                    </div>
                    <span class="btn btn-primary px-6 py-2.5">Tinjau pendaftar &rarr;</span>
                </a>
            @endif

            {{-- Ringkasan --}}
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-navy p-5 text-paper-white">
                    <p class="text-sm text-white/70">Total informasi</p>
                    <p class="mt-2 text-heading font-bold tabular-nums" x-data="hitung({{ $total }})" x-text="n">{{ $total }}</p>
                    <p class="mt-1 text-sm text-white/60">magang, lomba, dan beasiswa</p>
                </div>
                <div class="rounded-2xl border border-fog bg-paper-white p-5">
                    <p class="flex items-center gap-2 text-sm text-graphite"><span class="size-2 rounded-full bg-[#16a34a]" aria-hidden="true"></span>Sedang dibuka</p>
                    <p class="mt-2 text-heading font-bold tabular-nums text-carbon" x-data="hitung({{ $dibuka }})" x-text="n">{{ $dibuka }}</p>
                    <p class="mt-1 text-sm text-graphite">bisa dilihat pembaca sekarang</p>
                </div>
                <div class="rounded-2xl border border-fog bg-paper-white p-5">
                    <p class="text-sm text-graphite">Status akun</p>
                    <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-mint-wash px-3 py-1 text-sm font-semibold text-[#15803d]">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                        Disetujui admin
                    </p>
                    <a href="{{ route('mitra.profil.show') }}" class="mt-3 block text-sm font-medium text-accent-dark hover:underline">Ubah profil organisasi &rarr;</a>
                </div>
            </div>

            {{-- Per kategori --}}
            <div>
                <h2 class="text-subheading font-semibold">Informasi Anda</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    @foreach ($ringkasan as $kunci => $data)
                        <div class="flex flex-col rounded-2xl border border-fog bg-paper-white p-5">
                            <div class="flex items-center gap-3">
                                <span class="flex size-10 items-center justify-center rounded-xl bg-navy text-accent">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $ikonInfo[$kunci] !!}</svg>
                                </span>
                                <p class="flex-1 font-semibold">{{ $data['label'] }}</p>
                                <p class="text-heading-sm font-bold tabular-nums">{{ $data['total'] }}</p>
                            </div>

                            <div class="mt-5 flex h-2.5 gap-0.5 overflow-hidden rounded-full bg-mist" role="img"
                                 aria-label="{{ $data['label'] }}: {{ $data['dibuka'] }} dibuka, {{ $data['ditutup'] }} ditutup, {{ $data['disembunyikan'] }} disembunyikan admin">
                                @foreach ($statusInfo as $status => [$nama, $warna])
                                    @if ($data[$status] > 0)
                                        <span class="{{ $warna }} h-full first:rounded-l-full last:rounded-r-full" style="width: {{ $data[$status] / max($data['total'], 1) * 100 }}%" title="{{ $nama }}: {{ $data[$status] }}"></span>
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

                            <div class="mt-5 grid grid-cols-2 gap-2">
                                <a href="{{ route('mitra.informasi.index', $kunci) }}" class="btn btn-outline py-2.5">Kelola</a>
                                <a href="{{ route('mitra.informasi.create', $kunci) }}" class="btn btn-primary py-2.5">+ Tambah</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Cara kerja --}}
            <div class="rounded-2xl border border-fog bg-paper-white p-5 sm:p-6">
                <h2 class="font-semibold">Cara kerja penayangan</h2>
                <ol class="mt-4 grid gap-4 sm:grid-cols-3">
                    @foreach ([
                        ['Tambah informasi', 'Isi deskripsi, persyaratan, batas waktu, kontak, dan tautan pendaftaran.'],
                        ['Langsung tayang', 'Mahasiswa dan dosen bisa melihatnya sampai batas pendaftaran lewat.'],
                        ['Pendaftaran di situs Anda', 'Pembaca diarahkan ke tautan pendaftaran resmi milik Anda.'],
                    ] as $i => [$judul, $teks])
                        <li class="flex gap-3">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-accent text-sm font-bold text-navy">{{ $i + 1 }}</span>
                            <div>
                                <p class="font-semibold">{{ $judul }}</p>
                                <p class="mt-0.5 text-sm text-graphite">{{ $teks }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>
@endsection

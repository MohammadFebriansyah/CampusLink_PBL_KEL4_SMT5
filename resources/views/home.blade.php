@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    {{-- Hero: navy + aksen oranye. Ilustrasi di kiri, teks dan tombol kategori di kanan. --}}
    @php
        $fotoHero = file_exists(public_path('images/hero-mahasiswa.png')) ? asset('images/hero-mahasiswa.png') : null;

        // Kartu melayang memakai info asli: satu info dengan tenggat terdekat dari tiap kategori.
        $gayaKategori = [
            'magang' => ['garis' => 'border-primary', 'label' => 'text-primary'],
            'lomba' => ['garis' => 'border-accent', 'label' => 'text-accent-dark'],
            'beasiswa' => ['garis' => 'border-[#16a34a]', 'label' => 'text-[#15803d]'],
        ];
        $sorotan = collect($kategori)->map(fn ($label, $kunci) => ($informasi[$kunci] ?? collect())->first()
            ? ['kunci' => $kunci, 'label' => $label, 'item' => $informasi[$kunci]->first()] : null)
            ->filter()->values();

        $ikonKategori = [
            'magang' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 13h18"/>',
            'lomba' => '<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4Z"/><path d="M17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/>',
            'beasiswa' => '<path d="m12 4 10 5-10 5L2 9l10-5Z"/><path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5M22 9v6"/>',
        ];
        $posisiKartu = [
            ['kelas' => 'top-[8%] left-0', 'depth' => '14px', 'gerak' => 'animate-float', 'delay' => '500ms'],
            ['kelas' => 'top-[50%] right-0', 'depth' => '-16px', 'gerak' => 'animate-float-slow [animation-delay:-3s]', 'delay' => '700ms'],
            ['kelas' => 'bottom-[10%] left-[2%] hidden sm:block', 'depth' => '20px', 'gerak' => 'animate-float [animation-delay:-5s]', 'delay' => '900ms'],
        ];
    @endphp
    <section class="relative isolate overflow-hidden bg-navy text-paper-white">
        <div class="hero-dots pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -top-48 right-[-10%] size-[520px] rounded-full bg-primary/50 blur-3xl animate-glow" aria-hidden="true"></div>
        <div class="pointer-events-none absolute bottom-[-30%] left-[-10%] size-[480px] rounded-full bg-accent/20 blur-3xl animate-glow [animation-delay:-3s]" aria-hidden="true"></div>

        <div class="relative mx-auto grid max-w-page grid-cols-1 items-center gap-10 px-4 pt-12 sm:px-6 md:pt-16 lg:min-h-[calc(100svh-4rem)] lg:grid-cols-[1fr_1.05fr] lg:gap-12 lg:pt-6">

            {{-- Kolom visual (kiri di desktop, bawah di HP) --}}
            <div class="relative order-last mx-auto h-[400px] w-full max-w-[540px] sm:h-[500px] lg:order-first lg:h-[min(620px,calc(100svh-6rem))] lg:self-end"
                 x-data="{ mx: 0, my: 0 }"
                 @mousemove="const r = $el.getBoundingClientRect(); mx = ((($event.clientX - r.left) / r.width) - 0.5) * 2; my = ((($event.clientY - r.top) / r.height) - 0.5) * 2"
                 @mouseleave="mx = 0; my = 0"
                 :style="`--mx: ${mx}; --my: ${my}`">

                {{-- Lingkaran oranye + orbit putus-putus di belakang orang --}}
                <div class="absolute bottom-[-12%] left-1/2 aspect-square w-[92%] -translate-x-1/2" aria-hidden="true">
                    <div class="absolute inset-0 rounded-full border-2 border-dashed border-white/15 animate-spin-slow"></div>
                    <div class="absolute inset-[9%] rounded-full border border-white/10 animate-spin-rev">
                        <span class="absolute top-[6%] left-[14%] size-3 rounded-full bg-accent shadow-[0_0_16px_4px_rgb(249_115_22/0.6)]"></span>
                        <span class="absolute right-[4%] bottom-[30%] size-2 rounded-full bg-paper-white/70"></span>
                    </div>
                    <div class="absolute inset-[18%] rounded-full bg-linear-to-br from-accent via-[#fb923c] to-[#fdba74] shadow-[inset_0_-30px_60px_rgb(194_65_12/0.45)]"></div>
                </div>

                {{-- Orang --}}
                <div class="hero-float inset-x-0 bottom-0 z-10 flex h-[88%] justify-center" style="--depth: -6px">
                    <div class="h-full animate-bob">
                        @if ($fotoHero)
                            <img src="{{ $fotoHero }}" alt="Mahasiswa Polinema" class="h-full w-auto object-contain object-bottom drop-shadow-[0_30px_40px_rgb(0_0_0/0.35)]">
                        @else
                            @include('partials.hero-ilustrasi')
                        @endif
                    </div>
                </div>

                {{-- Lencana kecil --}}
                <div class="hero-float top-[2%] right-[6%]" style="--depth: -10px">
                    <div class="animate-float-slow">
                        <span class="inline-flex animate-pop-in items-center gap-1.5 rounded-full bg-[#16a34a] px-3 py-1.5 text-xs font-semibold text-paper-white shadow-lg [animation-delay:400ms]">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                            Mitra terverifikasi
                        </span>
                    </div>
                </div>

                {{-- Kartu info asli. Bila belum ada info, tampil kartu ajakan. --}}
                @forelse ($sorotan as $i => $s)
                    @php $pos = $posisiKartu[$i]; $gaya = $gayaKategori[$s['kunci']]; @endphp
                    <div class="hero-float {{ $pos['kelas'] }}" style="--depth: {{ $pos['depth'] }}">
                        <div class="{{ $pos['gerak'] }}">
                            <a href="{{ route($s['kunci'] . '.show', $s['item']) }}" class="sorotan-card animate-pop-in {{ $gaya['garis'] }}" style="animation-delay: {{ $pos['delay'] }}">
                                <span class="text-[10px] font-bold tracking-wider uppercase {{ $gaya['label'] }}">{{ $s['label'] }}</span>
                                <span class="truncate text-sm font-semibold text-carbon">{{ $s['item']->judul }}</span>
                                <span class="flex items-center justify-between gap-2">
                                    <span class="truncate text-xs text-graphite">{{ $s['item']->penyelenggara }}</span>
                                    <span class="shrink-0 rounded-full bg-accent-wash px-2 py-0.5 text-[11px] font-semibold text-accent-dark">{{ \App\Support\Tanggal::labelSisa($s['item']->batas_pendaftaran) }}</span>
                                </span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="hero-float {{ $posisiKartu[0]['kelas'] }}" style="--depth: 14px">
                        <div class="animate-float">
                            <div class="sorotan-card animate-pop-in border-accent [animation-delay:500ms]">
                                <span class="text-[10px] font-bold tracking-wider text-accent-dark uppercase">Segera hadir</span>
                                <span class="text-sm font-semibold text-carbon">Peluang dari mitra kampus</span>
                                <span class="text-xs text-graphite">Magang, lomba, dan beasiswa</span>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Kolom teks (kanan di desktop) --}}
            <div class="lg:py-16">
                <p class="inline-flex animate-rise items-center gap-2 text-sm font-semibold text-accent">
                    <span class="h-px w-8 bg-accent"></span>
                    CampusLink · Politeknik Negeri Malang
                </p>

                <h1 class="mt-5 animate-rise text-[36px] leading-[1.1] font-bold tracking-[-1.2px] text-balance [animation-delay:80ms] sm:text-[48px] lg:text-[56px] lg:tracking-[-2px]"
                    x-data="{ kata: ['Magang', 'Lomba', 'Beasiswa'], i: 0 }"
                    x-init="setInterval(() => i = (i + 1) % kata.length, 2800)">
                    Satu pintu menuju
                    <span class="sr-only">magang, lomba, dan beasiswa</span>
                    <span aria-hidden="true" class="relative inline-block">
                        <template x-for="(k, idx) in kata" :key="k">
                            <span x-show="i === idx" class="relative inline-block animate-word">
                                <span x-text="k"></span>
                                {{-- Garis bawah oranye yang tergambar ulang tiap kata berganti --}}
                                <svg class="absolute -bottom-2 left-0 h-3 w-full text-accent" viewBox="0 0 200 12" preserveAspectRatio="none" fill="none">
                                    <path d="M3 8c40-6 90-7 194-2" stroke="currentColor" stroke-width="5" stroke-linecap="round" pathLength="1" stroke-dasharray="1" class="animate-draw"/>
                                </svg>
                            </span>
                        </template>
                        <noscript>Magang</noscript>
                    </span>
                    untuk mahasiswa Polinema.
                </h1>

                <p class="mt-6 max-w-xl animate-rise text-subheading leading-relaxed text-white/75 [animation-delay:160ms]">
                    Informasi magang, lomba, dan beasiswa dari mitra yang diverifikasi admin, ditambah direktori
                    untuk menemukan rekan tim dan dosen pembimbing.
                </p>

                {{-- Tiga pintu kategori dengan jumlah info yang masih dibuka --}}
                <div class="mt-8 grid animate-rise grid-cols-1 gap-3 [animation-delay:240ms] sm:grid-cols-3">
                    @foreach ($kategori as $kunci => $label)
                        <a href="{{ route($kunci . '.index') }}"
                           class="group flex items-center gap-3 rounded-2xl bg-white/[0.06] p-4 ring-1 ring-white/10 transition hover:-translate-y-0.5 hover:bg-white/10 hover:ring-accent/60 sm:flex-col sm:items-start">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-accent text-navy">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $ikonKategori[$kunci] !!}</svg>
                            </span>
                            <span class="flex-1">
                                <span class="block font-semibold">{{ $label }}</span>
                                <span class="block text-xs text-white/60">
                                    <span x-data="hitung({{ (int) ($jumlah[$kunci] ?? 0) }})" x-text="n">{{ (int) ($jumlah[$kunci] ?? 0) }}</span> dibuka
                                </span>
                            </span>
                            <svg class="size-4 text-white/40 transition group-hover:translate-x-1 group-hover:text-accent sm:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </a>
                    @endforeach
                </div>

                <div class="mt-8 flex animate-rise flex-wrap items-center gap-3 [animation-delay:320ms]">
                    <a href="{{ route('register') }}" class="btn btn-primary px-6 py-3">Buat akun gratis</a>
                    <a href="{{ route('direktori.index') }}" class="btn px-5 py-3 text-paper-white ring-1 ring-white/25 hover:bg-white/10">Buka direktori</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Peluang terbaru (data asli dari database) --}}
    <section class="bg-[#f7f8fc]">
        <div class="mx-auto grid max-w-page grid-cols-1 gap-10 px-4 py-16 sm:px-6 md:py-24 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
            <div class="lg:sticky lg:top-28">
                <p class="text-sm font-semibold text-accent-dark">Peluang terbaru</p>
                <h2 class="mt-2 text-heading font-semibold text-balance">Batas pendaftaran terdekat, jangan sampai terlewat</h2>
                <p class="mt-4 max-w-md text-graphite">
                    Tiga informasi dengan tenggat paling dekat dari tiap kategori. Semua ditayangkan oleh mitra yang sudah diverifikasi admin.
                </p>
                <a href="{{ route('magang.index') }}" class="btn btn-primary mt-8">Lihat semua informasi</a>
            </div>

            <div x-data="{ tab: 'magang' }" class="rounded-3xl bg-paper-white p-3 shadow-[0_24px_60px_-30px_rgb(15_40_120/0.35)] ring-1 ring-primary/10 sm:p-4">
                <div class="flex flex-wrap items-center justify-between gap-3 p-2">
                    <div class="flex gap-1 rounded-full bg-mist p-1" role="tablist">
                        @foreach ($kategori as $kunci => $label)
                            <button type="button" role="tab"
                                    class="rounded-full px-4 py-2 text-sm font-medium tracking-[-0.32px] text-graphite transition hover:text-carbon"
                                    :class="{ 'bg-paper-white text-primary! shadow-subtle': tab === '{{ $kunci }}' }"
                                    :aria-selected="tab === '{{ $kunci }}'"
                                    @click="tab = '{{ $kunci }}'">{{ $label }}</button>
                        @endforeach
                    </div>
                    <div class="text-sm font-medium tracking-[-0.32px] text-primary">
                        <a x-show="tab === 'magang'" href="{{ route('magang.index') }}" class="hover:underline">Semua magang &rarr;</a>
                        <a x-show="tab === 'lomba'" x-cloak href="{{ route('lomba.index') }}" class="hover:underline">Semua lomba &rarr;</a>
                        <a x-show="tab === 'beasiswa'" x-cloak href="{{ route('beasiswa.index') }}" class="hover:underline">Semua beasiswa &rarr;</a>
                    </div>
                </div>

                @foreach ($kategori as $kunci => $label)
                    <div x-show="tab === '{{ $kunci }}'" {{ $loop->first ? '' : 'x-cloak' }} role="tabpanel" class="mt-2 space-y-2">
                        @forelse ($informasi[$kunci] as $item)
                            <a href="{{ route($kunci . '.show', $item) }}" class="group flex items-center gap-4 rounded-2xl border border-fog p-4 transition hover:border-primary/30 hover:bg-primary-wash/60">
                                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-sm font-bold text-primary" aria-hidden="true">
                                    {{ mb_strtoupper(mb_substr($item->penyelenggara, 0, 2)) }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold group-hover:text-primary">{{ $item->judul }}</p>
                                    <p class="truncate text-caption text-graphite">{{ $item->penyelenggara }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-amber/15 px-3 py-1 text-caption font-semibold text-[#9a5a00]">{{ \App\Support\Tanggal::labelSisa($item->batas_pendaftaran) }}</span>
                            </a>
                        @empty
                            <div class="rounded-2xl border border-dashed border-fog py-12 text-center">
                                <p class="font-medium">Belum ada informasi {{ mb_strtolower($label) }} yang dibuka.</p>
                                <p class="mt-1 text-caption text-graphite">Cek lagi nanti, mitra baru menayangkan peluang setiap minggu.</p>
                            </div>
                        @endforelse
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Alasan memakai CampusLink --}}
    <section class="mx-auto max-w-page px-4 py-16 sm:px-6 md:py-24">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-semibold text-accent-dark">Kenapa CampusLink</p>
            <h2 class="mt-2 text-heading font-semibold text-balance">Peluang kampus tidak lagi tersebar di banyak grup</h2>
        </div>

        <div class="mt-12 grid gap-5 md:grid-cols-3">
            @foreach ([
                ['Satu tempat untuk semua', 'Magang, lomba, dan beasiswa tampil bersama, lengkap dengan persyaratan dan batas waktu.', '<path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 13 9 5 9-5"/><path d="m3 17 9 5 9-5"/>', 'bg-primary/10 text-primary'],
                ['Mitra diverifikasi admin', 'Mitra boleh memakai email apa saja, tetapi baru bisa menayangkan informasi setelah admin menyetujui akunnya.', '<path d="M12 3 4 6v6c0 4.5 3.4 8.2 8 9 4.6-.8 8-4.5 8-9V6l-8-3Z"/><path d="m9 12 2 2 4-4"/>', 'bg-mint/15 text-[#1f8a3c]'],
                ['Daftar di situs penyelenggara', 'Setiap informasi memuat kontak dan tautan pendaftaran resmi, jadi seleksi tetap dilakukan penyelenggara.', '<path d="M14 3h7v7"/><path d="M21 3 11 13"/><path d="M19 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h5"/>', 'bg-amber/15 text-[#b26a00]'],
            ] as [$judul, $teks, $path, $warna])
                <div class="rounded-3xl border border-fog bg-paper-white p-7 transition hover:-translate-y-1 hover:border-primary/20 hover:shadow-[0_20px_40px_-24px_rgb(15_40_120/0.35)]">
                    <span class="flex size-12 items-center justify-center rounded-2xl {{ $warna }}">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $path !!}</svg>
                    </span>
                    <h3 class="mt-5 text-subheading font-semibold">{{ $judul }}</h3>
                    <p class="mt-2 text-graphite">{{ $teks }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Direktori --}}
    <section class="mx-auto grid max-w-page grid-cols-1 items-center gap-12 px-4 pb-16 sm:px-6 md:grid-cols-2 md:pb-24">
        <div>
            <p class="text-sm font-semibold text-accent-dark">Direktori</p>
            <h2 class="mt-2 text-heading font-semibold text-balance">Temukan rekan tim dan dosen pembimbing</h2>
            <p class="mt-4 max-w-md text-graphite">
                Direktori memuat profil singkat mahasiswa dan dosen. Saring berdasarkan bidang keahlian, kelas,
                jurusan, program studi, dan angkatan.
            </p>
            <ul class="mt-6 space-y-2 text-graphite">
                @foreach (['Cari berdasarkan keahlian', 'Lihat jurusan, prodi, dan angkatan', 'Hanya untuk mahasiswa dan dosen yang sudah masuk'] as $poin)
                    <li class="flex items-center gap-2">
                        <span class="flex size-5 items-center justify-center rounded-full bg-primary/10 text-primary" aria-hidden="true">
                            <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5 9-10"/></svg>
                        </span>
                        {{ $poin }}
                    </li>
                @endforeach
            </ul>
            <a href="{{ route('direktori.index') }}" class="btn btn-outline mt-8">Buka direktori &rarr;</a>
        </div>

        {{-- Contoh tampilan fiktif. Profil asli hanya terlihat oleh mahasiswa dan dosen yang sudah masuk. --}}
        <div class="relative">
            <div class="absolute inset-0 -z-10 rounded-[40px] md:-inset-6 bg-linear-to-br from-accent/15 via-primary/10 to-transparent blur-2xl" aria-hidden="true"></div>
            <div class="rounded-3xl border border-fog bg-paper-white p-6 shadow-[0_24px_60px_-30px_rgb(15_40_120/0.35)]">
                <div class="flex items-center justify-between">
                    <p class="text-caption font-medium text-ash">Contoh tampilan</p>
                    <span class="rounded-full bg-mint/15 px-2.5 py-0.5 text-caption font-semibold text-[#1f8a3c]">Mencari tim</span>
                </div>
                <div class="mt-4 flex items-start gap-4">
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-navy text-sm font-bold text-accent" aria-hidden="true">NM</span>
                    <div class="min-w-0">
                        <p class="font-semibold">Nama Mahasiswa</p>
                        <p class="text-caption text-graphite">Sistem Informasi Bisnis, SIB-3A, angkatan 2024</p>
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span class="rounded-full bg-primary/10 px-3 py-1 text-caption font-medium text-primary">Web Development</span>
                            <span class="rounded-full bg-primary/10 px-3 py-1 text-caption font-medium text-primary">UI/UX Design</span>
                            <span class="rounded-full bg-primary/10 px-3 py-1 text-caption font-medium text-primary">Basis Data</span>
                        </div>
                    </div>
                </div>
                <div class="mt-5 flex items-center gap-3 rounded-2xl bg-mist p-3">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-paper-white text-caption font-bold text-graphite" aria-hidden="true">ND</span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium">Nama Dosen</p>
                        <p class="text-caption text-graphite">Dosen pembimbing, Teknologi Informasi</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Untuk mitra: pita navy selebar layar --}}
    <section class="relative isolate overflow-hidden bg-navy text-paper-white">
        <div class="hero-dots pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -top-32 right-0 size-[420px] rounded-full bg-primary/50 blur-3xl" aria-hidden="true"></div>
        <div class="relative mx-auto grid max-w-page items-center gap-12 px-4 py-16 sm:px-6 md:grid-cols-2 md:py-24">
            <div>
                <p class="text-sm font-semibold text-accent">Untuk mitra</p>
                <h2 class="mt-2 text-heading font-semibold text-balance">Punya program untuk mahasiswa Polinema?</h2>
                <p class="mt-4 max-w-md text-white/75">
                    Mitra menayangkan magang, lomba, dan beasiswa langsung kepada mahasiswa dan dosen.
                    Pendaftaran tetap berjalan di situs Anda.
                </p>
                <a href="{{ route('register', ['peran' => 'mitra']) }}" class="btn mt-8 bg-orange-500 px-6 py-3 font-semibold text-slate-900 shadow-lg hover:bg-orange-400">Daftar sebagai mitra</a>
            </div>

            <ol class="space-y-3">
                @foreach ([
                    ['Daftar dengan email apa saja', 'Gmail, email kampus, atau email organisasi.'],
                    ['Tunggu verifikasi admin', 'Admin memeriksa akun sebelum Anda bisa menayangkan informasi.'],
                    ['Tayangkan peluang Anda', 'Isi deskripsi, persyaratan, batas waktu, kontak, dan tautan pendaftaran.'],
                ] as $i => [$judul, $teks])
                    <li class="flex gap-4 rounded-2xl bg-white/8 p-5 ring-1 ring-white/12 backdrop-blur">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-accent text-sm font-bold text-navy">{{ $i + 1 }}</span>
                        <div>
                            <p class="font-semibold">{{ $judul }}</p>
                            <p class="mt-0.5 text-white/70">{{ $teks }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
@endsection

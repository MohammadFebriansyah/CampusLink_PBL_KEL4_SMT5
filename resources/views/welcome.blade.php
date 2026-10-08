<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>CampusLink — Platform Profesional Khusus Mahasiswa</title>
    <meta name="description" content="Wadah profesional terverifikasi khusus mahasiswa domain institusi (.ac.id). Pamerkan portofolio, temukan magang, beasiswa, dan rekrut rekan tim lintas jurusan.">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Inter:wght@100..900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface font-sans text-on-surface antialiased text-sm leading-5">

    {{-- ═══════════════════════════════════════════
         HEADER / NAVBAR
    ═══════════════════════════════════════════ --}}
    <header class="fixed top-0 left-0 right-0 z-50 bg-paper-white/95 backdrop-blur-md border-b border-fog">
        <div class="h-16 max-w-7xl mx-auto px-margin-mobile lg:px-margin flex items-center justify-between">

            {{-- Logo --}}
            <div class="flex items-center gap-4">
                <a class="flex items-center gap-2" href="{{ url('/') }}">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-primary to-secondary flex items-center justify-center text-paper-white font-semibold text-base shadow-xs">C</div>
                    <span class="font-display font-semibold text-xl tracking-tight text-carbon">Campus<span class="text-primary">Link</span></span>
                </a>
                <span class="hidden sm:inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200/80">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>.ac.id verified
                </span>
            </div>

            {{-- Desktop Nav --}}
            <nav class="hidden lg:flex items-center gap-2">
                <a class="px-3 py-1.5 text-sm font-medium text-primary font-semibold transition-colors" href="#">Tentang</a>
                <a class="px-3 py-1.5 text-sm font-medium text-graphite hover:text-carbon transition-colors" href="#papan-peluang">Papan Peluang</a>
                <a class="px-3 py-1.5 text-sm font-medium text-graphite hover:text-carbon transition-colors" href="#artikel-wawasan">Artikel & Wawasan</a>
                <a class="px-3 py-1.5 text-sm font-medium text-graphite hover:text-carbon transition-colors" href="#">Mitra Kampus</a>
            </nav>

            {{-- Auth Buttons --}}
            <div class="flex items-center gap-2">
                <a class="hidden md:inline-flex px-3 py-1.5 text-sm font-medium text-graphite hover:text-carbon transition-colors" href="{{ route('login') }}">Masuk</a>
                <a class="px-4 py-2 rounded-full text-sm font-medium bg-gradient-to-r from-primary to-secondary text-paper-white hover:brightness-105 shadow-xs transition-all" href="{{ route('register.dosen') }}">Daftar Akun (.ac.id)</a>
            </div>
        </div>
    </header>

    {{-- ═══════════════════════════════════════════
         MAIN CONTENT
    ═══════════════════════════════════════════ --}}
    <main class="w-full pt-16 bg-surface min-h-[calc(100vh-16rem)]">
        <div class="flex flex-col w-full">

            {{-- ───────── HERO SECTION ───────── --}}
            <section class="relative w-full max-w-7xl mx-auto px-margin-mobile lg:px-margin pt-8 pb-8 overflow-hidden">

                {{-- Background Gradient Blob --}}
                <div class="pointer-events-none absolute -top-24 left-1/2 -translate-x-1/2 w-[720px] h-[340px] bg-gradient-to-r from-violet-200/50 via-indigo-200/40 to-sky-200/40 blur-3xl rounded-full opacity-70 -z-10"></div>

                <div class="flex flex-col items-center text-center gap-6 max-w-3xl mx-auto relative z-10">

                    {{-- Badge --}}
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold shadow-xs">
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span>Ekosistem Kolaborasi & Karir Mahasiswa .ac.id</span>
                    </div>

                    {{-- Headline --}}
                    <h1 class="font-display font-bold text-4xl md:text-6xl text-carbon tracking-[-0.04em] leading-tight">
                        Bangun <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary to-indigo-600">Portofolio Nyata</span>.
                        Raih Peluang Emas.
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-700 via-purple-700 to-primary">Temukan Tim.</span>
                    </h1>

                    {{-- Subtitle --}}
                    <p class="text-base text-graphite max-w-2xl leading-relaxed">
                        Wadah profesional terverifikasi khusus mahasiswa domain institusi (<span class="text-primary font-medium">.ac.id</span>).
                        Pamerkan karya capstone, temukan magang MBKM, beasiswa riset, dan rekrut rekan tim lintas jurusan tanpa bias senioritas.
                    </p>

                    {{-- Email Verify Form --}}
                    <div class="w-full max-w-md">
                        <form class="p-1.5 pl-4 rounded-full bg-paper-white border border-indigo-200/70 shadow-sm flex items-center justify-between gap-1 transition-all focus-within:border-primary focus-within:ring-2 focus-within:ring-indigo-100" onsubmit="event.preventDefault(); document.getElementById('verify-feedback').classList.remove('hidden');">
                            <input class="w-full bg-transparent text-sm text-carbon placeholder:text-ash focus:outline-none" id="hero-email" placeholder="nama@mahasiswa.polinema.ac.id" required type="email">
                            <button class="px-4 py-2.5 rounded-full bg-gradient-to-r from-primary to-secondary text-paper-white text-sm font-medium shadow-sm hover:shadow-md hover:from-primary hover:to-indigo-800 transition-all shrink-0" type="submit">Cek Akses</button>
                        </form>
                        <div class="hidden mt-2 text-center text-emerald-700 text-xs" id="verify-feedback">✓ Domain institusi terverifikasi. Siap untuk SSO kampus.</div>
                    </div>

                    {{-- CTA Buttons --}}
                    <div class="flex flex-wrap items-center justify-center gap-4 pt-1">
                        <a class="px-6 py-2.5 rounded-full bg-gradient-to-r from-primary to-indigo-600 text-paper-white text-sm font-medium shadow hover:shadow-indigo-200 hover:brightness-105 transition-all" href="#papan-peluang">Mulai Eksplorasi</a>
                        <a class="px-6 py-2.5 rounded-full bg-paper-white border border-indigo-200 text-carbon text-sm font-medium hover:bg-linen hover:border-indigo-300 transition-colors shadow-xs" href="#papan-peluang">Cari Lowongan Magang</a>
                    </div>
                </div>

                {{-- Stats Row --}}
                <div class="w-full grid grid-cols-1 md:grid-cols-3 gap-gutter mt-8 pt-8 border-t border-fog text-left">
                    <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100/80 space-y-1">
                        <div class="font-display font-bold text-3xl bg-clip-text text-transparent bg-gradient-to-r from-primary to-indigo-600">100%</div>
                        <div class="font-semibold text-base text-carbon">Khusus Domain .ac.id</div>
                        <p class="text-xs text-graphite leading-relaxed">Otentikasi mutlak surel kampus, bebas bot dan loker fiktif dengan verifikasi kredensial mahasiswa resmi.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-100/80 space-y-1">
                        <div class="font-display font-bold text-3xl bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-700">450+</div>
                        <div class="font-semibold text-base text-carbon">Proyek Nyata Terunggah</div>
                        <p class="text-xs text-graphite leading-relaxed">Repositori tugas besar PBL terkurasi, prototype Figma interaktif, dan paper ilmiah berbasis capstone.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-purple-50/50 border border-purple-100/80 space-y-1">
                        <div class="font-display font-bold text-3xl bg-clip-text text-transparent bg-gradient-to-r from-secondary to-purple-600">120+</div>
                        <div class="font-semibold text-base text-carbon">Peluang Terverifikasi</div>
                        <p class="text-xs text-graphite leading-relaxed">Magang MBKM bersertifikat, beasiswa riset institusi, dan pendanaan PKM Dikti yang siap didaftar.</p>
                    </div>
                </div>
            </section>

            {{-- ───────── PARTNER LOGOS STRIP ───────── --}}
            <section class="w-full border-y border-fog bg-linen/50 py-6">
                <div class="max-w-7xl mx-auto px-margin-mobile lg:px-margin text-center">
                    <div class="text-xs font-semibold uppercase tracking-wider text-ash mb-4">Terintegrasi dengan Ekosistem Akademik & Industri</div>
                    <div class="flex flex-wrap items-center justify-center gap-x-8 gap-y-4 text-graphite font-semibold text-base">
                        <span>Telkom Indonesia</span>
                        <span>Bank Mandiri</span>
                        <span>GoTo Ecosystem</span>
                        <span>Paragon Tech</span>
                        <span>Astra International</span>
                        <span>JTI Polinema</span>
                        <span>Kemendiktisaintek RI</span>
                    </div>
                </div>
            </section>

            {{-- ───────── ABOUT / FEATURES SECTION ───────── --}}
            <section class="w-full max-w-7xl mx-auto px-margin-mobile lg:px-margin py-8">
                <div class="max-w-2xl mb-8 space-y-1">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-indigo-50 text-primary text-xs font-semibold border border-indigo-100 uppercase tracking-wider">Mengenal CampusLink</div>
                    <h2 class="font-display font-semibold text-3xl text-carbon">Dirancang Spesifik Menjawab Kebutuhan Mahasiswa Aktif</h2>
                    <p class="text-base text-graphite">Platform profesional konvensional kerap membuat karya mahasiswa tenggelam. CampusLink hadir memberi panggung setara, terkurasi, dan bebas distraksi.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter">

                    {{-- Feature 1: Verifikasi --}}
                    <div class="p-6 rounded-2xl border border-blue-100 bg-paper-white flex flex-col justify-between gap-4 hover:border-blue-300 hover:shadow-md transition-all relative overflow-hidden group">
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-400 to-blue-600"></div>
                        <div class="space-y-2">
                            <div class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 text-xs font-semibold uppercase tracking-wider">01 / Validitas</div>
                            <h3 class="font-display font-semibold text-xl text-carbon">Verifikasi Eksklusif .ac.id</h3>
                            <p class="text-sm text-graphite leading-relaxed">Akses tertutup khusus civitas akademika terdaftar. Menjamin lingkungan bebas loker palsu dan profiling transparan.</p>
                        </div>
                        <div class="text-xs font-semibold text-blue-700 pt-4 border-t border-fog font-medium flex items-center justify-between">
                            <span>SSO Kampus Terintegrasi</span>
                            <span class="text-blue-500 font-bold">→</span>
                        </div>
                    </div>

                    {{-- Feature 2: Portofolio --}}
                    <div class="p-6 rounded-2xl border border-violet-100 bg-paper-white flex flex-col justify-between gap-4 hover:border-violet-300 hover:shadow-md transition-all relative overflow-hidden group">
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-violet-400 to-purple-600"></div>
                        <div class="space-y-2">
                            <div class="inline-flex items-center px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-xs font-semibold uppercase tracking-wider">02 / Portofolio</div>
                            <h3 class="font-display font-semibold text-xl text-carbon">Profil & Karya Nyata</h3>
                            <p class="text-sm text-graphite leading-relaxed">Bukan resume teks pasif. Tampilkan repositori GitHub aktif, prototype interaktif Figma, serta laporan proyek capstone.</p>
                        </div>
                        <div class="text-xs font-semibold text-purple-700 pt-4 border-t border-fog font-medium flex items-center justify-between">
                            <span>Embed Artifact Terbuka</span>
                            <span class="text-purple-500 font-bold">→</span>
                        </div>
                    </div>

                    {{-- Feature 3: Karir --}}
                    <div class="p-6 rounded-2xl border border-emerald-100 bg-paper-white flex flex-col justify-between gap-4 hover:border-emerald-300 hover:shadow-md transition-all relative overflow-hidden group">
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-600"></div>
                        <div class="space-y-2">
                            <div class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-xs font-semibold uppercase tracking-wider">03 / Karir</div>
                            <h3 class="font-display font-semibold text-xl text-carbon">Papan Peluang Terkurasi</h3>
                            <p class="text-sm text-graphite leading-relaxed">Jalur langsung ke program Magang Bersertifikat Industri, pendanaan PKM Dikti, hibah riset skripsi, dan kompetisi inovasi.</p>
                        </div>
                        <div class="text-xs font-semibold text-emerald-700 pt-4 border-t border-fog font-medium flex items-center justify-between">
                            <span>Konversi SKS MBKM</span>
                            <span class="text-emerald-500 font-bold">→</span>
                        </div>
                    </div>

                    {{-- Feature 4: Kolaborasi --}}
                    <div class="p-6 rounded-2xl border border-amber-100 bg-paper-white flex flex-col justify-between gap-4 hover:border-amber-300 hover:shadow-md transition-all relative overflow-hidden group">
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-orange-500"></div>
                        <div class="space-y-2">
                            <div class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 text-xs font-semibold uppercase tracking-wider">04 / Kolaborasi</div>
                            <h3 class="font-display font-semibold text-xl text-carbon">Project Open Call</h3>
                            <p class="text-sm text-graphite leading-relaxed">Memiliki ide produk kompetisi atau riset? Buka panggilan kolaborasi rekan tim multidisiplin lintas fakultas.</p>
                        </div>
                        <div class="text-xs font-semibold text-amber-800 pt-4 border-t border-fog font-medium flex items-center justify-between">
                            <span>Matchmaking Otomatis</span>
                            <span class="text-amber-600 font-bold">→</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ───────── PAPAN PELUANG (OPPORTUNITIES) ───────── --}}
            <section class="w-full max-w-7xl mx-auto px-margin-mobile lg:px-margin py-8 border-t border-fog" id="papan-peluang">

                {{-- Section Header --}}
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-6 gap-4">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-ash">Peluang Terkini</div>
                        <h2 class="font-display font-semibold text-3xl text-carbon">Papan Informasi Peluang Terkurasi</h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-1">
                        <button class="opp-filter-btn px-3 py-1.5 rounded-full text-sm font-medium bg-linen border border-fog text-carbon transition-colors" onclick="filterOpportunities('all', this)">Semua Peluang</button>
                        <button class="opp-filter-btn px-3 py-1.5 rounded-full text-sm font-medium text-graphite hover:text-carbon transition-colors" onclick="filterOpportunities('magang', this)">Magang & MBKM</button>
                        <button class="opp-filter-btn px-3 py-1.5 rounded-full text-sm font-medium text-graphite hover:text-carbon transition-colors" onclick="filterOpportunities('lomba', this)">Kompetisi</button>
                        <button class="opp-filter-btn px-3 py-1.5 rounded-full text-sm font-medium text-graphite hover:text-carbon transition-colors" onclick="filterOpportunities('beasiswa', this)">Beasiswa Riset</button>
                    </div>
                </div>

                {{-- Opportunities Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter" id="opp-grid">

                    {{-- Card 1: Data Scientist Intern --}}
                    <div class="opp-card p-6 rounded-2xl border border-fog bg-paper-white flex flex-col justify-between gap-4 hover:border-indigo-300 hover:shadow-md transition-all" data-category="magang">
                        <div>
                            <div class="flex items-center justify-between text-xs font-semibold mb-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 font-medium">Magang Industri</span>
                                <span class="text-ash font-medium">Deadline: 18 Nov 2026</span>
                            </div>
                            <h3 class="font-display font-semibold text-xl text-carbon">Data Scientist & Analytics Intern</h3>
                            <p class="text-sm text-graphite mt-1">PT Bank Rakyat Indonesia (Persero) Tbk — Divisi AI & Big Data</p>
                            <div class="mt-4 space-y-1 text-xs text-graphite">
                                <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Jakarta (Hybrid / Fleksibel)</div>
                                <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Uang Saku Standar Industri + Konversi 20 SKS MBKM</div>
                                <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Syarat: Min. Semester 5, Pemahaman Python, SQL, & ML</div>
                            </div>
                        </div>
                        <div class="pt-4 flex items-center justify-between border-t border-fog">
                            <span class="text-xs font-semibold text-emerald-700 font-medium flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Mitra Terverifikasi</span>
                            <a class="px-4 py-1.5 rounded-full bg-gradient-to-r from-primary to-secondary text-paper-white text-sm font-medium hover:brightness-105 shadow-xs transition-all" href="#">Lamar Cepat</a>
                        </div>
                    </div>

                    {{-- Card 2: Kompetisi --}}
                    <div class="opp-card p-6 rounded-2xl border border-fog bg-paper-white flex flex-col justify-between gap-4 hover:border-amber-300 hover:shadow-md transition-all" data-category="lomba">
                        <div>
                            <div class="flex items-center justify-between text-xs font-semibold mb-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 font-medium">Kompetisi Nasional</span>
                                <span class="text-ash font-medium">Deadline: 28 Nov 2026</span>
                            </div>
                            <h3 class="font-display font-semibold text-xl text-carbon">National Smart City & Green Tech Challenge 2026</h3>
                            <p class="text-sm text-graphite mt-1">PT Astra International Tbk x Kemendiktisaintek RI</p>
                            <div class="mt-4 space-y-1 text-xs text-graphite">
                                <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Total Hadiah Rp 120.000.000 + Inkubasi Startup Astra</div>
                                <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Format: Tim 3–5 Mahasiswa Multidisiplin Lintas Jurusan</div>
                                <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Output: Pitch Deck Inovasi & Video Prototipe</div>
                            </div>
                        </div>
                        <div class="pt-4 flex items-center justify-between border-t border-fog">
                            <span class="text-xs font-semibold text-blue-700 font-medium flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Registrasi Terbuka</span>
                            <a class="px-4 py-1.5 rounded-full border border-amber-200 bg-amber-50/50 text-amber-900 text-sm font-medium hover:bg-amber-100 transition-colors" href="#">Unduh Panduan</a>
                        </div>
                    </div>

                    {{-- Card 3: Cloud Engineering --}}
                    <div class="opp-card p-6 rounded-2xl border border-fog bg-paper-white flex flex-col justify-between gap-4 hover:border-purple-300 hover:shadow-md transition-all" data-category="magang">
                        <div>
                            <div class="flex items-center justify-between text-xs font-semibold mb-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-700 border border-purple-200 font-medium">Fast-Track Industri</span>
                                <span class="text-ash font-medium">Deadline: 05 Des 2026</span>
                            </div>
                            <h3 class="font-display font-semibold text-xl text-carbon">Cloud Engineering & DevOps Apprentice</h3>
                            <p class="text-sm text-graphite mt-1">Amazon Web Services (AWS) EduCloud Indonesia</p>
                            <div class="mt-4 space-y-1 text-xs text-graphite">
                                <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>Remote / Jam Kerja Fleksibel Menyesuaikan Kuliah</div>
                                <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>Voucher Ujian Sertifikasi AWS Solutions Architect Gratis</div>
                                <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>Mentoring 1-on-1 bersama Cloud Architect Senior</div>
                            </div>
                        </div>
                        <div class="pt-4 flex items-center justify-between border-t border-fog">
                            <span class="text-xs font-semibold text-purple-700 font-medium flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>Batch 2026/1</span>
                            <a class="px-4 py-1.5 rounded-full bg-gradient-to-r from-primary to-secondary text-paper-white text-sm font-medium hover:brightness-105 shadow-xs transition-all" href="#">Daftar Seleksi</a>
                        </div>
                    </div>

                    {{-- Card 4: Beasiswa Riset --}}
                    <div class="opp-card p-6 rounded-2xl border border-fog bg-paper-white flex flex-col justify-between gap-4 hover:border-emerald-300 hover:shadow-md transition-all" data-category="beasiswa">
                        <div>
                            <div class="flex items-center justify-between text-xs font-semibold mb-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-medium">Beasiswa Riset</span>
                                <span class="text-ash font-medium">Deadline: 15 Des 2026</span>
                            </div>
                            <h3 class="font-display font-semibold text-xl text-carbon">Beasiswa Unggulan Riset AI & Digital 2026</h3>
                            <p class="text-sm text-graphite mt-1">Paragon Technology and Innovation Foundation</p>
                            <div class="mt-4 space-y-1 text-xs text-graphite">
                                <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Pembiayaan UKT Penuh hingga Lulus + Dana Skripsi Rp 15 Juta</div>
                                <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Akses Fasilitas Lab R&D & Pembimbing Co-Supervisor Dosen</div>
                                <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Syarat: Mahasiswa D4/S1 dengan Proposal Inovasi Digital</div>
                            </div>
                        </div>
                        <div class="pt-4 flex items-center justify-between border-t border-fog">
                            <span class="text-xs font-semibold text-emerald-700 font-medium flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Jenjang D4/S1</span>
                            <a class="px-4 py-1.5 rounded-full bg-gradient-to-r from-emerald-600 to-teal-700 text-paper-white text-sm font-medium hover:brightness-105 shadow-xs transition-all" href="#">Lamar Beasiswa</a>
                        </div>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <a class="text-sm font-medium text-graphite hover:text-carbon transition-colors" href="#">Lihat Semua 120+ Peluang Terverifikasi →</a>
                </div>
            </section>

            {{-- ───────── ARTIKEL & WAWASAN ───────── --}}
            <section class="w-full max-w-7xl mx-auto px-margin-mobile lg:px-margin py-8 border-t border-fog" id="artikel-wawasan">

                <div class="max-w-2xl mb-6 space-y-1">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-indigo-50 text-primary text-xs font-semibold border border-indigo-100 uppercase tracking-wider">ARTIKEL & WAWASAN AKADEMIK</div>
                    <h2 class="font-display font-semibold text-3xl text-carbon">Artikel Terkini, Tips Karier, & Wawasan Sivitas Akademika</h2>
                    <p class="text-base text-graphite">Kumpulan artikel terkurasi dari dosen pembimbing, mahasiswa berprestasi, dan mitra industri seputar persiapan magang, publikasi ilmiah, dan teknologi.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">

                    {{-- Article 1 --}}
                    <article class="p-6 rounded-2xl border border-fog bg-paper-white flex flex-col justify-between gap-4 hover:border-lavender hover:shadow-md transition-all relative overflow-hidden group">
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-400 to-indigo-600"></div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs font-semibold">
                                <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 font-medium">TIPS MAGANG & KARIER</span>
                                <span class="text-ash font-medium">5 min baca</span>
                            </div>
                            <h3 class="font-display font-semibold text-xl text-carbon group-hover:text-primary transition-colors">Panduan Lolos Magang Bersertifikat BUMN & Startup Unicorn untuk Mahasiswa Semester 5</h3>
                            <p class="text-xs text-graphite leading-relaxed">Langkah taktis menyusun portofolio berbasis proyek PBL, melengkapi verifikasi SSO kampus, dan strategi menjawab wawancara teknis tanpa bias pengalaman kerja formal.</p>
                        </div>
                        <div>
                            <div class="pt-4 border-t border-fog flex items-center justify-between text-xs text-ash mb-2">
                                <span>Tim Karier CampusLink & CDC</span>
                                <span>24 Okt 2026</span>
                            </div>
                            <a class="text-sm font-medium text-primary hover:underline inline-flex items-center gap-1" href="#">Baca Artikel <span class="text-primary font-bold">→</span></a>
                        </div>
                    </article>

                    {{-- Article 2 --}}
                    <article class="p-6 rounded-2xl border border-fog bg-paper-white flex flex-col justify-between gap-4 hover:border-emerald-300 hover:shadow-md transition-all relative overflow-hidden group">
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-600"></div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs font-semibold">
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-medium">RISET & TEKNOLOGI</span>
                                <span class="text-ash font-medium">8 min baca</span>
                            </div>
                            <h3 class="font-display font-semibold text-xl text-carbon group-hover:text-emerald-700 transition-colors">Mempersiapkan Karya PBL & Capstone Menjadi Publikasi Terindeks Sinta / IEEE</h3>
                            <p class="text-xs text-graphite leading-relaxed">Framework sistematis mentransformasikan artefak capstone software atau hardware menjadi manuskrip ilmiah standar konferensi internasional bereputasi.</p>
                        </div>
                        <div>
                            <div class="pt-4 border-t border-fog flex items-center justify-between text-xs text-ash mb-2">
                                <span>Dian Hanifudin Subhi, M.Kom.</span>
                                <span>19 Okt 2026</span>
                            </div>
                            <a class="text-sm font-medium text-emerald-700 hover:underline inline-flex items-center gap-1" href="#">Baca Artikel <span class="text-emerald-500 font-bold">→</span></a>
                        </div>
                    </article>

                    {{-- Article 3 --}}
                    <article class="p-6 rounded-2xl border border-fog bg-paper-white flex flex-col justify-between gap-4 hover:border-amber-300 hover:shadow-md transition-all relative overflow-hidden group">
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-orange-500"></div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs font-semibold">
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 font-medium">KOLABORASI & HIBAH PKM</span>
                                <span class="text-ash font-medium">4 min baca</span>
                            </div>
                            <h3 class="font-display font-semibold text-xl text-carbon group-hover:text-amber-800 transition-colors">Strategi Membangun Tim Multidisiplin yang Solid untuk Lolos Pendanaan PKM Dikti</h3>
                            <p class="text-xs text-graphite leading-relaxed">Kombinasi kompetensi lintas departemen (Teknik, Desain, & Bisnis) yang terbukti memikat reviewer Dikti hingga tahap PIMNAS nasional.</p>
                        </div>
                        <div>
                            <div class="pt-4 border-t border-fog flex items-center justify-between text-xs text-ash mb-2">
                                <span>Derikho Nabima S.N (Ketua PBL)</span>
                                <span>15 Okt 2026</span>
                            </div>
                            <a class="text-sm font-medium text-amber-800 hover:underline inline-flex items-center gap-1" href="#">Baca Artikel <span class="text-amber-600 font-bold">→</span></a>
                        </div>
                    </article>
                </div>

                {{-- Write Article CTA --}}
                <div class="mt-6 p-6 rounded-2xl border border-fog bg-linen flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <div class="font-semibold text-base text-carbon">Punya Wawasan Riset atau Ingin Berbagi Pengalaman Akademik?</div>
                        <p class="text-xs text-graphite mt-1">Publikasikan artikel Anda dan jangkau 500+ perguruan tinggi se-Indonesia.</p>
                    </div>
                    <a class="px-4 py-2 rounded-full border border-indigo-200 bg-paper-white text-primary hover:bg-lavender hover:text-paper-white text-sm font-medium transition-colors shadow-xs shrink-0 inline-flex items-center gap-1" href="#">Tulis Artikel Baru →</a>
                </div>
            </section>

            {{-- ───────── CTA / FINAL REGISTRATION ───────── --}}
            <section class="w-full max-w-7xl mx-auto px-margin-mobile lg:px-margin pb-8">
                <div class="p-6 lg:p-12 rounded-3xl bg-gradient-to-br from-carbon via-[#1f1d3e] to-[#2b2766] border border-indigo-500/20 text-center space-y-4 max-w-3xl mx-auto shadow-xl relative overflow-hidden">

                    {{-- Decorative Blobs --}}
                    <div class="pointer-events-none absolute -right-20 -bottom-20 w-64 h-64 bg-indigo-500/20 rounded-full blur-2xl"></div>
                    <div class="pointer-events-none absolute -left-20 -top-20 w-64 h-64 bg-purple-500/20 rounded-full blur-2xl"></div>

                    <div class="relative z-10 space-y-4">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-secondary-fixed text-xs font-semibold uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Registrasi Semester Genap
                        </div>
                        <h2 class="font-display font-semibold text-3xl text-paper-white">Siap Menampilkan Portofolio & Meraih Peluang Emas?</h2>
                        <p class="text-base text-secondary-fixed/90 max-w-xl mx-auto leading-relaxed">Daftarkan akun menggunakan surel kampus resmi (&#64;mahasiswa.*.ac.id) untuk aktivasi SSO instan.</p>

                        <div class="w-full max-w-md mx-auto pt-1">
                            <form class="p-1.5 pl-4 rounded-full bg-white/10 backdrop-blur-md border border-white/30 flex items-center justify-between gap-1 transition-all focus-within:border-secondary-container focus-within:bg-white/15" onsubmit="event.preventDefault(); document.getElementById('final-confirm').classList.remove('hidden');">
                                <input class="w-full bg-transparent text-sm text-paper-white placeholder:text-outline-variant focus:outline-none" placeholder="nim@mahasiswa.ac.id" required type="email">
                                <button class="px-4 py-2.5 rounded-full bg-gradient-to-r from-primary-container to-secondary-container text-on-secondary-container text-sm font-semibold hover:brightness-110 shadow-sm transition-all shrink-0" type="submit">Daftar Akun</button>
                            </form>
                            <div class="hidden mt-2 text-center text-mint text-xs" id="final-confirm">✓ Tautan aktivasi SSO dikirimkan ke kotak masuk surel kampus Anda.</div>
                        </div>

                        <div class="pt-2 flex flex-wrap items-center justify-center gap-x-4 text-secondary-fixed-dim text-xs">
                            <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-mint"></span>100% Gratis</span>
                            <span>•</span>
                            <span>Data Akademik Terproteksi</span>
                            <span>•</span>
                            <span>500+ Kampus Indonesia</span>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </main>

    {{-- ═══════════════════════════════════════════
         FOOTER
    ═══════════════════════════════════════════ --}}
    <footer class="w-full bg-surface border-t border-fog py-8">
        <div class="max-w-7xl mx-auto px-margin-mobile lg:px-margin">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-gutter mb-8">

                {{-- Footer Branding --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-lavender flex items-center justify-center text-paper-white font-semibold text-base">C</div>
                        <span class="font-display font-semibold text-xl text-carbon tracking-tight">Campus<span class="text-primary">Link</span></span>
                    </div>
                    <p class="text-sm text-graphite max-w-md leading-relaxed">Platform profesional dan portofolio berstandar nasional untuk sivitas akademika mahasiswa Indonesia. Dibangun untuk percepatan karier, publikasi karya, dan sinergi industri.</p>
                    <div class="text-ash text-xs font-semibold">Domain Eksklusif Institusi (.ac.id)</div>
                </div>

                {{-- Footer Links 1 --}}
                <div class="lg:col-span-3 space-y-2">
                    <div class="font-semibold text-base text-carbon">Peluang & Proyek</div>
                    <ul class="space-y-1 text-sm text-graphite">
                        <li><a class="hover:text-carbon transition-colors" href="#">Papan Magang Nasional</a></li>
                        <li><a class="hover:text-carbon transition-colors" href="#">Project Open Call Mahasiswa</a></li>
                        <li><a class="hover:text-carbon transition-colors" href="#artikel-wawasan">Artikel & Publikasi</a></li>
                        <li><a class="hover:text-carbon transition-colors" href="#">Riset Kolaboratif Dosen</a></li>
                    </ul>
                </div>

                {{-- Footer Links 2 --}}
                <div class="lg:col-span-4 space-y-2">
                    <div class="font-semibold text-base text-carbon">Ekosistem Mitra</div>
                    <ul class="space-y-1 text-sm text-graphite">
                        <li><a class="hover:text-carbon transition-colors" href="#">JTI Polinema Innovation Hub</a></li>
                        <li><a class="hover:text-carbon transition-colors" href="#">Konsorsium Politeknik Negeri</a></li>
                        <li><a class="hover:text-carbon transition-colors" href="#">Partner Industri & Tech</a></li>
                        <li><a class="hover:text-carbon transition-colors" href="#">Standar Kredensial Akademik</a></li>
                    </ul>
                </div>
            </div>

            {{-- Footer Bottom --}}
            <div class="pt-6 border-t border-fog flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-ash">
                <p>© 2025 CampusLink. Dirancang dengan presisi untuk Mahasiswa Indonesia.</p>
                <div class="flex items-center gap-4">
                    <a class="hover:text-carbon transition-colors" href="#">Privasi Data</a>
                    <a class="hover:text-carbon transition-colors" href="#">Ketentuan Layanan</a>
                    <a class="hover:text-carbon transition-colors" href="#">Pusat Bantuan</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- ═══════════════════════════════════════════
         INLINE JAVASCRIPT
    ═══════════════════════════════════════════ --}}
    <script>
        function filterOpportunities(category, btnElement) {
            const buttons = document.querySelectorAll('.opp-filter-btn');
            buttons.forEach(b => {
                b.classList.remove('bg-lavender', 'text-paper-white', 'bg-linen', 'border', 'border-fog');
                b.classList.add('text-graphite');
            });
            btnElement.classList.add('bg-lavender', 'text-paper-white');
            btnElement.classList.remove('text-graphite');

            const cards = document.querySelectorAll('.opp-card');
            cards.forEach(card => {
                if (category === 'all' || card.getAttribute('data-category') === category) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>

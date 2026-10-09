<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Mahasiswa — CampusLink Portal Nasional</title>
    <meta name="description" content="Akses platform profesional terverifikasi sivitas akademika & mitra industri. Khusus mahasiswa aktif ber-email institusi.">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Inter:wght@100..900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface font-sans text-on-surface antialiased text-sm leading-5 min-h-screen flex flex-col justify-between selection:bg-indigo-100 selection:text-primary">

    {{-- ═══════════════════════════════════════════
         HEADER / NAVBAR
    ═══════════════════════════════════════════ --}}
    <header class="fixed top-0 left-0 right-0 z-50 bg-paper-white/90 backdrop-blur-md border-b border-fog/80">
        <div class="h-16 max-w-7xl mx-auto px-margin-mobile lg:px-margin flex items-center justify-between">
            
            {{-- Brand Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-primary to-secondary flex items-center justify-center text-paper-white font-bold text-base shadow-xs group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4 text-paper-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-display font-bold text-lg leading-tight tracking-tight text-carbon">Campus<span class="text-primary">Link</span></span>
                    <span class="text-[9px] font-semibold tracking-wider uppercase text-ash">PORTAL NASIONAL</span>
                </div>
            </a>

            {{-- SSO Badge Right --}}
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50/90 border border-emerald-200/80 text-emerald-700 text-xs font-semibold shadow-2xs">
                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>SSO Terpadu Pendidikan & Industri</span>
            </div>
        </div>
    </header>

    {{-- ═══════════════════════════════════════════
         MAIN CONTENT AREA
    ═══════════════════════════════════════════ --}}
    <main class="w-full flex-1 pt-24 pb-12 flex flex-col items-center justify-center px-4 relative overflow-hidden">
        
        {{-- Background Soft Gradient Glowing Blob --}}
        <div class="pointer-events-none absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[720px] h-[400px] bg-gradient-to-r from-violet-200/40 via-indigo-200/30 to-purple-200/40 blur-3xl rounded-full opacity-70 -z-10"></div>

        <div class="w-full max-w-[580px] flex flex-col items-center gap-6">

            {{-- REGISTER MAHASISWA CARD --}}
            <div class="w-full bg-paper-white rounded-[28px] shadow-xl border border-fog/80 overflow-hidden relative transition-all">
                
                {{-- Card Top Gradient Accent --}}
                <div class="h-1.5 w-full bg-gradient-to-r from-blue-500 via-indigo-500 to-pink-500"></div>

                <div class="p-6 sm:p-8 flex flex-col gap-6">
                    
                    {{-- Card Badges & Titles --}}
                    <div class="flex flex-col items-center text-center gap-2">
                        <div class="flex flex-wrap items-center justify-center gap-2">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-50/90 border border-purple-200/80 text-purple-700 text-xs font-semibold shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                </svg>
                                <span>Civitas Akademika .ac.id</span>
                            </div>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50/90 border border-emerald-200/80 text-emerald-700 text-xs font-semibold shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <span>SSO Terintegrasi</span>
                            </div>
                        </div>

                        <h1 class="font-display font-bold text-3xl sm:text-4xl text-carbon tracking-tight mt-1">
                            Daftar Akun Mahasiswa
                        </h1>

                        <p class="text-sm text-graphite max-w-md leading-relaxed">
                            Akses platform profesional terverifikasi sivitas akademika & mitra industri. Khusus mahasiswa aktif ber-email institusi.
                        </p>
                    </div>

                    {{-- ROLE TABS SELECTOR --}}
                    <div class="bg-[#f4f3f8] p-1.5 rounded-full flex items-center justify-between gap-1 shadow-inner border border-slate-200/50">
                        <div class="flex-1 py-2 px-3 rounded-full text-xs font-semibold flex items-center justify-center gap-1.5 bg-white text-carbon shadow-xs border border-slate-200/60">
                            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                            <span>Akses Mahasiswa</span>
                        </div>
                        <a href="{{ route('register.dosen') }}" class="flex-1 py-2 px-3 rounded-full text-xs font-semibold flex items-center justify-center gap-1.5 text-graphite hover:text-carbon transition-colors">
                            <svg class="w-4 h-4 shrink-0 text-graphite" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <span>Dosen</span>
                        </a>
                        <a href="{{ route('register.industry') }}" class="flex-1 py-2 px-3 rounded-full text-xs font-semibold flex items-center justify-center gap-1.5 text-graphite hover:text-carbon transition-colors truncate">
                            <svg class="w-4 h-4 shrink-0 text-graphite" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M9 8h.01M15 16h.01M15 12h.01M15 8h.01"/>
                            </svg>
                            <span>Mitra Industri</span>
                        </a>
                    </div>

                    {{-- Status Session Message --}}
                    @if (session('status'))
                        <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    {{-- Validation Errors Alert --}}
                    @if ($errors->any())
                        <div class="p-3.5 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-xs space-y-1">
                            <div class="font-semibold flex items-center gap-1.5 text-red-800">
                                <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Terdapat Kendala Pendaftaran:</span>
                            </div>
                            <ul class="list-disc list-inside pl-1 text-[11px] text-red-700 space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- REGISTER FORM --}}
                    <form method="POST" action="{{ route('register.mahasiswa.store') }}" class="flex flex-col gap-4">
                        @csrf

                        {{-- 1. Nama Lengkap --}}
                        <div class="flex flex-col gap-1.5">
                            <label for="name" class="text-xs font-semibold text-carbon uppercase tracking-wider">
                                NAMA LENGKAP (SESUAI KTM / SIAKAD) <span class="text-ember">*</span>
                            </label>
                            
                            <div class="relative rounded-2xl bg-[#f8f8fb] border @error('name') border-red-300 @else border-fog @enderror focus-within:border-primary focus-within:bg-paper-white focus-within:ring-2 focus-within:ring-indigo-100 transition-all flex items-center px-4 py-3 gap-3">
                                <svg class="w-4 h-4 text-ash shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 012-2h2a2 2 0 012 2v1m-6 0h6"/>
                                </svg>
                                <input 
                                    type="text" 
                                    name="name" 
                                    id="name" 
                                    value="{{ old('name') }}" 
                                    required 
                                    placeholder="Contoh: Derikho Nabima S.N" 
                                    class="w-full bg-transparent text-sm text-carbon placeholder:text-ash focus:outline-none font-sans"
                                >
                            </div>
                        </div>

                        {{-- 2. Email Institusi (.ac.id) --}}
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <label for="email" class="text-xs font-semibold text-carbon uppercase tracking-wider">
                                    EMAIL INSTITUSI (.AC.ID ONLY) <span class="text-ember">*</span>
                                </label>
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600">
                                    <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Domain aktif (.ac.id)
                                </span>
                            </div>

                            <div class="relative rounded-2xl bg-[#f8f8fb] border @error('email') border-red-300 @else border-fog @enderror focus-within:border-primary focus-within:bg-paper-white focus-within:ring-2 focus-within:ring-indigo-100 transition-all flex items-center px-4 py-3 gap-3">
                                <svg class="w-4 h-4 text-ash shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <input 
                                    type="email" 
                                    name="email" 
                                    id="email" 
                                    value="{{ old('email') }}" 
                                    required 
                                    placeholder="nama@mahasiswa.polinema.ac.id" 
                                    class="w-full bg-transparent text-sm text-carbon placeholder:text-ash focus:outline-none font-sans"
                                >
                                <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-semibold text-xs border border-indigo-100/80 shrink-0 select-none">
                                    .ac.id
                                </span>
                            </div>
                        </div>

                        {{-- 3 & 4. Grid Row: NIM & Program Studi --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- NIM --}}
                            <div class="flex flex-col gap-1.5">
                                <label for="nim" class="text-xs font-semibold text-carbon uppercase tracking-wider">
                                    NOMOR INDUK MAHASISWA (NIM) <span class="text-ember">*</span>
                                </label>

                                <div class="relative rounded-2xl bg-[#f8f8fb] border @error('nim') border-red-300 @else border-fog @enderror focus-within:border-primary focus-within:bg-paper-white focus-within:ring-2 focus-within:ring-indigo-100 transition-all flex items-center px-4 py-3 gap-3">
                                    <span class="text-ash font-bold text-sm shrink-0 select-none">#</span>
                                    <input 
                                        type="text" 
                                        name="nim" 
                                        id="nim" 
                                        value="{{ old('nim') }}" 
                                        required 
                                        placeholder="244107060050" 
                                        class="w-full bg-transparent text-sm text-carbon placeholder:text-ash focus:outline-none font-sans"
                                    >
                                </div>
                            </div>

                            {{-- Program Studi / Kampus --}}
                            <div class="flex flex-col gap-1.5">
                                <label for="study_program" class="text-xs font-semibold text-carbon uppercase tracking-wider">
                                    PROGRAM STUDI / KAMPUS <span class="text-ember">*</span>
                                </label>

                                <div class="relative rounded-2xl bg-[#f8f8fb] border @error('study_program') border-red-300 @else border-fog @enderror focus-within:border-primary focus-within:bg-paper-white focus-within:ring-2 focus-within:ring-indigo-100 transition-all flex items-center px-4 py-3 gap-3">
                                    <svg class="w-4 h-4 text-ash shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M9 8h.01M15 16h.01M15 12h.01M15 8h.01"/>
                                    </svg>
                                    <input 
                                        type="text" 
                                        name="study_program" 
                                        id="study_program" 
                                        value="{{ old('study_program') }}" 
                                        required 
                                        placeholder="D-IV SIB - Polinema" 
                                        class="w-full bg-transparent text-sm text-carbon placeholder:text-ash focus:outline-none font-sans"
                                    >
                                </div>
                            </div>
                        </div>

                        {{-- 5 & 6. Grid Row: Kata Sandi & Konfirmasi Sandi --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Kata Sandi --}}
                            <div class="flex flex-col gap-1.5">
                                <label for="password" class="text-xs font-semibold text-carbon uppercase tracking-wider">
                                    KATA SANDI <span class="text-ember">*</span>
                                </label>

                                <div class="relative rounded-2xl bg-[#f8f8fb] border @error('password') border-red-300 @else border-fog @enderror focus-within:border-primary focus-within:bg-paper-white focus-within:ring-2 focus-within:ring-indigo-100 transition-all flex items-center px-4 py-3 gap-3">
                                    <svg class="w-4 h-4 text-ash shrink-0 select-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <input 
                                        type="password" 
                                        name="password" 
                                        id="password" 
                                        required 
                                        placeholder="••••••••" 
                                        class="w-full bg-transparent text-sm text-carbon placeholder:text-ash focus:outline-none font-sans tracking-wider"
                                    >
                                    <button type="button" onclick="toggleVisibility('password', 'eye-icon-1')" class="text-ash hover:text-graphite focus:outline-none cursor-pointer shrink-0">
                                        <svg id="eye-icon-1" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Konfirmasi Sandi --}}
                            <div class="flex flex-col gap-1.5">
                                <label for="password_confirmation" class="text-xs font-semibold text-carbon uppercase tracking-wider">
                                    KONFIRMASI SANDI <span class="text-ember">*</span>
                                </label>

                                <div class="relative rounded-2xl bg-[#f8f8fb] border border-fog focus-within:border-primary focus-within:bg-paper-white focus-within:ring-2 focus-within:ring-indigo-100 transition-all flex items-center px-4 py-3 gap-3">
                                    <svg class="w-4 h-4 text-ash shrink-0 select-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <input 
                                        type="password" 
                                        name="password_confirmation" 
                                        id="password_confirmation" 
                                        required 
                                        placeholder="••••••••" 
                                        class="w-full bg-transparent text-sm text-carbon placeholder:text-ash focus:outline-none font-sans tracking-wider"
                                    >
                                    <button type="button" onclick="toggleVisibility('password_confirmation', 'eye-icon-2')" class="text-ash hover:text-graphite focus:outline-none cursor-pointer shrink-0">
                                        <svg id="eye-icon-2" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- 7. Checkbox Agreement --}}
                        <div class="pt-1">
                            <label class="inline-flex items-start gap-2.5 cursor-pointer select-none">
                                <input 
                                    type="checkbox" 
                                    name="terms" 
                                    id="terms" 
                                    class="w-4 h-4 mt-0.5 rounded border-fog text-primary focus:ring-primary/20 accent-primary cursor-pointer shrink-0" 
                                    required
                                >
                                <span class="text-xs text-graphite leading-relaxed">
                                    Saya menyetujui <a href="#" class="font-medium text-indigo-600 hover:underline">Ketentuan Layanan</a> & <a href="#" class="font-medium text-indigo-600 hover:underline">Kebijakan Privasi Riset</a> Sivitas Kampus.
                                </span>
                            </label>
                        </div>

                        {{-- 8. Submit Button --}}
                        <button 
                            type="submit" 
                            class="w-full mt-2 py-3.5 px-6 rounded-full bg-gradient-to-r from-indigo-500 via-indigo-600 to-purple-600 text-paper-white font-medium text-sm shadow-md hover:brightness-105 hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <span>Daftar Akun Mahasiswa</span>
                            <span class="font-bold">→</span>
                        </button>
                    </form>

                    {{-- 9. Login Redirect Link --}}
                    <div class="text-center text-xs text-graphite pt-1 border-t border-fog/60">
                        Sudah memiliki akun? <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline transition-colors">Masuk ke Platform</a>
                    </div>
                </div>
            </div>

            {{-- SUB-CARD AUDIT BADGES --}}
            <div class="flex flex-col items-center gap-2 text-center">
                <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-xs text-graphite/90 font-medium">
                    <span class="flex items-center gap-1 text-emerald-600 font-semibold">
                        <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        SSL 256-bit Encrypted
                    </span>
                    <span class="text-ash">•</span>
                    <span class="flex items-center gap-1">
                        ID-JKT Server Cluster
                    </span>
                    <span class="text-ash">•</span>
                    <span class="flex items-center gap-1">
                        Terintegrasi PDDIKTI / Kemendikbud
                    </span>
                </div>
                
                <p class="text-[11px] text-ash leading-relaxed max-w-md">
                    © 2025 CampusLink Indonesia. Terintegrasi dengan Sistem Informasi Akademik Nasional.
                </p>
            </div>

        </div>
    </main>

    {{-- ═══════════════════════════════════════════
         BOTTOM FOOTER BAR
    ═══════════════════════════════════════════ --}}
    <footer class="w-full border-t border-fog bg-paper-white/80 backdrop-blur-md px-margin-mobile lg:px-margin py-3.5">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-ash">
            <div class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-ash shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Enkripsi TLS 1.3 & Protokol Keamanan Kemendikbudristek RI</span>
            </div>

            <div class="flex items-center gap-6">
                <a href="#" class="hover:text-carbon transition-colors">Pusat Bantuan</a>
                <a href="#" class="hover:text-carbon transition-colors">Kebijakan Privasi</a>
                <span>© 2025 CampusLink Ecosystem</span>
            </div>
        </div>
    </footer>

    {{-- ═══════════════════════════════════════════
         JAVASCRIPT
    ═══════════════════════════════════════════ --}}
    <script>
        function toggleVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a8.959 8.959 0 013.982-.963c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m-4.692-4.692a3 3 0 00-4.243-4.243" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                `;
            } else {
                input.type = 'password';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                `;
            }
        }
    </script>
</body>
</html>

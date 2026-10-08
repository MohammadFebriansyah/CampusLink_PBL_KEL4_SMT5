<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Dosen — CampusLink Portal Nasional</title>
    <meta name="description" content="Kelola bimbingan PBL/Capstone, publikasi riset terindeks, dan jembatani talenta mahasiswa ke industri.">

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
                    C
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

            {{-- REGISTER DOSEN CARD --}}
            <div class="w-full bg-paper-white rounded-[28px] shadow-xl border border-fog/80 overflow-hidden relative transition-all">
                
                {{-- Card Top Gradient Accent --}}
                <div class="h-1.5 w-full bg-gradient-to-r from-emerald-400 via-primary to-secondary"></div>

                <div class="p-6 sm:p-8 flex flex-col gap-6">
                    
                    {{-- Card Badge & Titles --}}
                    <div class="flex flex-col items-center text-center gap-2">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-50/90 border border-emerald-200/80 text-emerald-800 text-xs font-semibold shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>PENDIDIK & PENELITI TERVERIFIKASI</span>
                            <span class="text-emerald-400">•</span>
                            <span class="text-emerald-700 font-medium">Validasi NIDN / NIP</span>
                        </div>

                        <h1 class="font-display font-bold text-3xl sm:text-4xl text-carbon tracking-tight mt-1">
                            Daftar Akun Dosen
                        </h1>

                        <p class="text-sm text-graphite max-w-md leading-relaxed">
                            Kelola bimbingan PBL/Capstone, publikasi riset terindeks, dan jembatani talenta mahasiswa ke industri.
                        </p>
                    </div>

                    {{-- ROLE TABS SELECTOR --}}
                    <div class="bg-[#f4f3f8] p-1.5 rounded-full flex items-center justify-between gap-1 shadow-inner border border-slate-200/50">
                        <a href="{{ route('login') }}" class="flex-1 py-2 px-3 rounded-full text-xs font-semibold flex items-center justify-center gap-1.5 text-graphite hover:text-carbon transition-colors">
                            <span>Mahasiswa</span>
                        </a>
                        <div class="flex-1 py-2 px-3 rounded-full text-xs font-semibold flex items-center justify-center gap-1.5 bg-gradient-to-r from-primary to-secondary text-paper-white shadow-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-paper-white"></span>
                            <span>Dosen (Pengajar)</span>
                        </div>
                        <a href="#" class="flex-1 py-2 px-3 rounded-full text-xs font-semibold flex items-center justify-center gap-1.5 text-graphite hover:text-carbon transition-colors truncate">
                            <span>Mitra Industri</span>
                        </a>
                    </div>

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
                    <form method="POST" action="{{ route('register.dosen.store') }}" class="flex flex-col gap-4">
                        @csrf

                        {{-- 1. Nama Lengkap & Gelar --}}
                        <div class="flex flex-col gap-1.5">
                            <label for="name" class="text-xs font-semibold text-carbon uppercase tracking-wider">
                                Nama Lengkap & Gelar Akademik <span class="text-ember">*</span>
                            </label>
                            
                            <div class="relative rounded-2xl bg-[#f8f8fb] border @error('name') border-red-300 @else border-fog @enderror focus-within:border-primary focus-within:bg-paper-white focus-within:ring-2 focus-within:ring-indigo-100 transition-all flex items-center px-4 py-3 gap-3">
                                <svg class="w-4 h-4 text-ash shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <input 
                                    type="text" 
                                    name="name" 
                                    id="name" 
                                    value="{{ old('name') }}" 
                                    required 
                                    placeholder="Dian Hanifudin Subhi, S.Kom., M.Kom." 
                                    class="w-full bg-transparent text-sm text-carbon placeholder:text-ash focus:outline-none font-sans"
                                >
                            </div>
                        </div>

                        {{-- 2. NIDN / NIP --}}
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <label for="nidn_nip" class="text-xs font-semibold text-carbon uppercase tracking-wider">
                                    NIDN / NIP <span class="text-ember">*</span>
                                </label>
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-primary">
                                    <svg class="w-3 h-3 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    PD-DIKTI Sync
                                </span>
                            </div>

                            <div class="relative rounded-2xl bg-[#f8f8fb] border @error('nidn_nip') border-red-300 @else border-fog @enderror focus-within:border-primary focus-within:bg-paper-white focus-within:ring-2 focus-within:ring-indigo-100 transition-all flex items-center px-4 py-3 gap-3">
                                <svg class="w-4 h-4 text-ash shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 012-2h2a2 2 0 012 2v1m-6 0h6"/>
                                </svg>
                                <input 
                                    type="text" 
                                    name="nidn_nip" 
                                    id="nidn_nip" 
                                    value="{{ old('nidn_nip') }}" 
                                    required 
                                    placeholder="0019088501 / 198508192015041001" 
                                    class="w-full bg-transparent text-sm text-carbon placeholder:text-ash focus:outline-none font-sans"
                                >
                            </div>
                        </div>

                        {{-- 3. Email Institusi (.ac.id) --}}
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <label for="email" class="text-xs font-semibold text-carbon uppercase tracking-wider">
                                    Email Institusi Dosen (.AC.ID) <span class="text-ember">*</span>
                                </label>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-semibold border border-emerald-200">
                                    <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Domain aktif (.ac.id)
                                </span>
                            </div>

                            <div class="relative rounded-2xl bg-[#f8f8fb] border @error('email') border-red-300 @else border-fog @enderror focus-within:border-primary focus-within:bg-paper-white focus-within:ring-2 focus-within:ring-indigo-100 transition-all flex items-center px-4 py-3 gap-3">
                                <span class="text-ash font-bold text-base shrink-0 select-none">@</span>
                                <input 
                                    type="email" 
                                    name="email" 
                                    id="email" 
                                    value="{{ old('email') }}" 
                                    required 
                                    placeholder="dian.hanifudin@polinema.ac.id" 
                                    class="w-full bg-transparent text-sm text-carbon placeholder:text-ash focus:outline-none font-sans"
                                >
                            </div>
                        </div>

                        {{-- 4. Homebase Perguruan Tinggi & Fakultas / Jurusan --}}
                        <div class="flex flex-col gap-1.5">
                            <label for="homebase" class="text-xs font-semibold text-carbon uppercase tracking-wider">
                                Homebase Perguruan Tinggi & Fakultas / Jurusan <span class="text-ember">*</span>
                            </label>

                            <div class="relative rounded-2xl bg-[#f8f8fb] border @error('homebase') border-red-300 @else border-fog @enderror focus-within:border-primary focus-within:bg-paper-white focus-within:ring-2 focus-within:ring-indigo-100 transition-all flex items-center px-4 py-3 gap-3">
                                <svg class="w-4 h-4 text-ash shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M9 8h.01M15 16h.01M15 12h.01M15 8h.01"/>
                                </svg>
                                <input 
                                    type="text" 
                                    name="homebase" 
                                    id="homebase" 
                                    value="{{ old('homebase') }}" 
                                    required 
                                    placeholder="Politeknik Negeri Malang - Jurusan Teknologi Informasi" 
                                    class="w-full bg-transparent text-sm text-carbon placeholder:text-ash focus:outline-none font-sans"
                                >
                            </div>
                        </div>

                        {{-- 5. Bidang Keahlian Utama (Fokus Riset) --}}
                        <div class="flex flex-col gap-1.5">
                            <label for="expertise" class="text-xs font-semibold text-carbon uppercase tracking-wider">
                                Bidang Keahlian Utama (Fokus Riset) <span class="text-ember">*</span>
                            </label>

                            <div class="relative rounded-2xl bg-[#f8f8fb] border @error('expertise') border-red-300 @else border-fog @enderror focus-within:border-primary focus-within:bg-paper-white focus-within:ring-2 focus-within:ring-indigo-100 transition-all flex items-center px-4 py-3 gap-3">
                                <svg class="w-4 h-4 text-ash shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                </svg>
                                <input 
                                    type="text" 
                                    name="expertise" 
                                    id="expertise" 
                                    value="{{ old('expertise') }}" 
                                    required 
                                    placeholder="Web Engineering, Distributed Systems, Cloud Architecture" 
                                    class="w-full bg-transparent text-sm text-carbon placeholder:text-ash focus:outline-none font-sans"
                                >
                            </div>
                        </div>

                        {{-- 6. Kata Sandi --}}
                        <div class="flex flex-col gap-1.5">
                            <label for="password" class="text-xs font-semibold text-carbon uppercase tracking-wider">
                                Kata Sandi <span class="text-ember">*</span>
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
                                    placeholder="Minimal 8 karakter kombinasi alfanumerik" 
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

                        {{-- 7. Konfirmasi Kata Sandi --}}
                        <div class="flex flex-col gap-1.5">
                            <label for="password_confirmation" class="text-xs font-semibold text-carbon uppercase tracking-wider">
                                Konfirmasi Kata Sandi <span class="text-ember">*</span>
                            </label>

                            <div class="relative rounded-2xl bg-[#f8f8fb] border border-fog focus-within:border-primary focus-within:bg-paper-white focus-within:ring-2 focus-within:ring-indigo-100 transition-all flex items-center px-4 py-3 gap-3">
                                <svg class="w-4 h-4 text-ash shrink-0 select-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <input 
                                    type="password" 
                                    name="password_confirmation" 
                                    id="password_confirmation" 
                                    required 
                                    placeholder="Ulangi kata sandi" 
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

                        {{-- 8. Checkbox Pakta Integritas --}}
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
                                    Saya menyatakan data pengajar & NIDN adalah sah terdaftar di <strong class="text-carbon">PD-DIKTI</strong> dan bersedia mematuhi pakta integritas bimbingan riset.
                                </span>
                            </label>
                        </div>

                        {{-- 9. Submit Button --}}
                        <button 
                            type="submit" 
                            class="w-full mt-2 py-3.5 px-6 rounded-full bg-gradient-to-r from-primary to-secondary text-paper-white font-medium text-sm shadow-md hover:brightness-105 hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <span>Daftar Akun Dosen</span>
                            <span class="font-bold">→</span>
                        </button>
                    </form>

                    {{-- 10. Login Redirect Link --}}
                    <div class="text-center text-xs text-graphite pt-1 border-t border-fog/60">
                        Sudah memiliki akun? <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline transition-colors">Masuk ke Platform</a>
                    </div>
                </div>
            </div>

            {{-- SUB-CARD AUDIT BADGES --}}
            <div class="flex flex-col items-center gap-2 text-center">
                <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-xs text-graphite/90 font-medium">
                    <span class="flex items-center gap-1">
                        <span>🛡️</span> SSL 256-bit Encrypted
                    </span>
                    <span class="text-ash">•</span>
                    <span class="flex items-center gap-1">
                        <span>🗄️</span> ID-JKT Server Cluster
                    </span>
                    <span class="text-ash">•</span>
                    <span class="flex items-center gap-1">
                        <span>🛡️</span> Terintegrasi PDDIKTI / Kemendikbud
                    </span>
                </div>
                
                <p class="text-[11px] text-ash leading-relaxed max-w-md">
                    © 2025 CampusLink Indonesia. Terintegrasi dengan Sistem Informasi Akademik Nasional & Kemendikbudristek RI.
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

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Mitra Perusahaan — CampusLink Portal Nasional</title>
    <meta name="description" content="Buka lowongan magang bersertifikat, rekrut talenta mahasiswa terverifikasi, dan kolaborasi riset industri nasional.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Inter:wght@100..900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">

    <header class="auth-header">
        <div class="auth-header-container">
            <a href="{{ url('/') }}" class="brand-logo">
                <div class="brand-logo-icon">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="brand-logo-text">Campus<span>Link</span></span>
                    <span class="brand-logo-sub">PORTAL NASIONAL</span>
                </div>
            </a>

            <div class="badge-sso">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>SSO Terpadu Pendidikan & Industri</span>
            </div>
        </div>
    </header>

    <main class="auth-main">
        <div class="auth-bg-blob"></div>

        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-card-topbar"></div>

                <div class="auth-card-body">
                    <div class="auth-card-header">
                        <div class="badge-mitra">
                            <span class="pulse-dot"></span>
                            <span>Mitra Industri & Rekruter Resmi</span>
                            <span class="divider">•</span>
                            <span class="highlight">Non-.ac.id (Domain Perusahaan)</span>
                        </div>

                        <h1 class="auth-title">
                            Daftar Akun Mitra Perusahaan
                        </h1>

                        <p class="auth-subtitle">
                            Buka lowongan magang bersertifikat, rekrut talenta mahasiswa terverifikasi, dan kolaborasi riset industri nasional.
                        </p>
                    </div>

                    <div class="role-switcher">
                        <a href="{{ route('register.mahasiswa') }}" class="role-tab">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                            </svg>
                            <span>Mahasiswa</span>
                        </a>
                        <a href="{{ route('register.dosen') }}" class="role-tab">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <span>Dosen</span>
                        </a>
                        <div class="role-tab active">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M9 8h.01M15 16h.01M15 12h.01M15 8h.01"/>
                            </svg>
                            <span>Akses Mitra / Korporasi</span>
                        </div>
                    </div>

                    <form class="auth-form">
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="pic_name" class="form-label">
                                    Nama Lengkap PIC / Rekruter <span class="req">*</span>
                                </label>
                                <div class="input-container">
                                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <input type="text" id="pic_name" placeholder="e.g. Budi Santoso" class="input-control">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="pic_position" class="form-label">
                                    Jabatan / Posisi <span class="req">*</span>
                                </label>
                                <div class="input-container">
                                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <input type="text" id="pic_position" placeholder="Talent Acquisition Lead" class="input-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-label-row">
                                <label for="company_name" class="form-label">
                                    Nama Entitas Perusahaan / Industri <span class="req">*</span>
                                </label>
                                <span class="form-hint">Sesuai Legalitas Resmi / NIB</span>
                            </div>
                            <div class="input-container">
                                <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M9 8h.01M15 16h.01M15 12h.01M15 8h.01"/>
                                </svg>
                                <input type="text" id="company_name" placeholder="PT Telkom Indonesia (Persero) Tbk" class="input-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-label-row">
                                <label for="email" class="form-label">
                                    Email Korporat Resmi <span class="req">*</span>
                                </label>
                                <span class="badge-corporate">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Email Resmi Perusahaan (Bukan .ac.id)
                                </span>
                            </div>
                            <div class="input-container">
                                <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <input type="email" id="email" placeholder="recruitment@telkom.co.id" class="input-control">
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="industry_category" class="form-label">
                                    Kategori Industri <span class="req">*</span>
                                </label>
                                <div class="input-container">
                                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                    <select id="industry_category" class="select-control">
                                        <option value="" disabled selected>Pilih Bidang Industri</option>
                                        <option value="it_software">Teknologi Informasi & Perangkat Lunak</option>
                                        <option value="telecommunications">Telekomunikasi & Jaringan</option>
                                        <option value="finance_banking">Keuangan, Perbankan & Fintech</option>
                                        <option value="manufacturing">Manufaktur, Otomotif & Industri Berat</option>
                                        <option value="creative_media">Media, Komunikasi & Industri Kreatif</option>
                                        <option value="healthcare">Kesehatan, Farmasi & Bioteknologi</option>
                                        <option value="consulting">Konsultan, Riset & Layanan Bisnis</option>
                                        <option value="energy">Energi, Minyak & Sumber Daya Alam</option>
                                        <option value="logistics">Logistik & Transportasi</option>
                                        <option value="other">Lainnya</option>
                                    </select>
                                    <svg class="select-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="linkedin_url" class="form-label">
                                    Profil Perusahaan / LinkedIn
                                </label>
                                <div class="input-container">
                                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                    <input type="url" id="linkedin_url" placeholder="https://linkedin.com/company/telkom" class="input-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="password" class="form-label">
                                    Kata Sandi <span class="req">*</span>
                                </label>
                                <div class="input-container">
                                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <input type="password" id="password" placeholder="Min. 8 Karakter" class="input-control">
                                    <button type="button" onclick="toggleVisibility('password', 'eye-icon-1')" class="btn-eye">
                                        <svg id="eye-icon-1" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation" class="form-label">
                                    Konfirmasi Sandi <span class="req">*</span>
                                </label>
                                <div class="input-container">
                                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <input type="password" id="password_confirmation" placeholder="Ulangi Kata Sandi" class="input-control">
                                    <button type="button" onclick="toggleVisibility('password_confirmation', 'eye-icon-2')" class="btn-eye">
                                        <svg id="eye-icon-2" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="terms-group">
                            <label class="terms-label">
                                <input type="checkbox" id="terms" class="terms-checkbox" checked>
                                <span class="terms-text">
                                    Saya menyetujui <a href="#" class="terms-link">Ketentuan Kemitraan CampusLink</a> &amp; <a href="#" class="terms-link">Pakta Integritas Perekrutan Kampus Merdeka</a>.
                                </span>
                            </label>
                        </div>

                        <button type="button" class="btn-submit-mitra">
                            <span>Daftar Akun Mitra Industri</span>
                            <span>→</span>
                        </button>
                    </form>

                    <div class="auth-redirect-row">
                        <span>Sudah memiliki akun?</span>
                        <a href="{{ route('login') }}" class="auth-redirect-link">
                            <span>Masuk ke Platform</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="trust-badges-container">
                <div class="trust-badges-list">
                    <span class="trust-badge-item">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>SSL 256-bit Encrypted</span>
                    </span>
                    <span class="trust-dot">•</span>
                    <span class="trust-badge-item">
                        <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                        <span>ID-JKT Server Cluster</span>
                    </span>
                    <span class="trust-dot">•</span>
                    <span class="trust-badge-item">
                        <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Verifikasi Legalitas NIB / Industri</span>
                    </span>
                </div>
                
                <p class="trust-text">
                    © 2025 CampusLink Indonesia. Terintegrasi dengan Sistem Informasi Akademik Nasional &amp; Kemendikbudristek RI.
                </p>
            </div>
        </div>
    </main>

    <footer class="auth-bottom-footer">
        <div class="auth-footer-container">
            <div class="footer-security-text">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Enkripsi TLS 1.3 &amp; Protokol Keamanan Kemendikbudristek RI</span>
            </div>

            <div class="footer-links">
                <a href="#">Pusat Bantuan</a>
                <a href="#">Kebijakan Privasi</a>
                <span>© 2025 CampusLink Ecosystem</span>
            </div>
        </div>
    </footer>

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

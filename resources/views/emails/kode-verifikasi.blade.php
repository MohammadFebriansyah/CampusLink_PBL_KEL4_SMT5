{{--
    Email kode OTP registrasi. Tata letak tabel dan gaya inline agar tampil sama di Gmail, Outlook, dan aplikasi
    email di HP (CSS eksternal dan flex/grid tidak didukung banyak klien email). Lebar maksimal 560px, menyempit di HP.
    Variabel: $kode, $nama, $menit
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Kode verifikasi CampusLink</title>
</head>
<body style="margin:0; padding:0; background-color:#f6f7fb; font-family:Arial, Helvetica, sans-serif; color:#181925;">
    {{-- Teks pratinjau di daftar kotak masuk (tanpa kode). --}}
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">Kode verifikasi pendaftaran CampusLink Anda, berlaku {{ $menit }} menit.</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f6f7fb;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;">
                    {{-- Logo --}}
                    <tr>
                        <td style="padding:0 4px 20px 4px; font-size:20px; font-weight:bold; letter-spacing:-0.4px; color:#0f1e3d;">
                            Campus<span style="color:#f97316;">Link</span>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:32px 28px;">
                            <p style="margin:0 0 8px 0; font-size:20px; font-weight:bold; color:#0f1e3d;">Verifikasi email Anda</p>
                            <p style="margin:0 0 20px 0; font-size:15px; line-height:1.6; color:#475569;">
                                Halo {{ $nama }}, masukkan kode berikut di halaman pendaftaran CampusLink untuk menyelesaikan pembuatan akun.
                            </p>

                            {{-- Kode --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="background-color:#f1f5f9; border-radius:12px; padding:20px 12px;">
                                        <span style="font-family:'Courier New', Courier, monospace; font-size:34px; font-weight:bold; letter-spacing:10px; color:#0f1e3d;">{{ $kode }}</span>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:20px 0 0 0; font-size:14px; line-height:1.6; color:#475569;">
                                Kode berlaku <strong style="color:#181925;">{{ $menit }} menit</strong> dan hanya bisa dipakai sekali.
                                Bila kode kedaluwarsa, minta kode baru di halaman verifikasi.
                            </p>

                            {{-- Peringatan --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:20px;">
                                <tr>
                                    <td style="background-color:#fff7ed; border-left:4px solid #f97316; border-radius:8px; padding:12px 14px; font-size:13px; line-height:1.6; color:#7c2d12;">
                                        <strong>Jangan bagikan kode ini kepada siapa pun</strong>, termasuk yang mengaku sebagai admin CampusLink.
                                        Kami tidak pernah meminta kode verifikasi lewat telepon, chat, atau email.
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:20px 0 0 0; font-size:13px; line-height:1.6; color:#64748b;">
                                Tidak merasa mendaftar di CampusLink? Abaikan email ini. Akun tidak akan dibuat tanpa kode di atas.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 4px 0 4px; font-size:12px; line-height:1.6; color:#94a3b8;">
                            Email otomatis dari {{ config('app.name') }}, jangan dibalas.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

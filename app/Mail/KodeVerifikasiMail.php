<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Kode OTP 6 angka untuk verifikasi email saat registrasi. Dikirim langsung (tidak lewat antrean) agar
 * kegagalan server email langsung diketahui dan ditampilkan ke pendaftar (lihat RegisterController).
 */
class KodeVerifikasiMail extends Mailable
{
    public function __construct(public string $kode, public string $nama, public int $menit) {}

    public function envelope(): Envelope
    {
        // Kode sengaja tidak dicantumkan di subjek: subjek tampil di notifikasi layar kunci dan pratinjau.
        return new Envelope(subject: 'Kode verifikasi pendaftaran CampusLink');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.kode-verifikasi',
            text: 'emails.kode-verifikasi-teks',
        );
    }
}

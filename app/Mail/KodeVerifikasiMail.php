<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class KodeVerifikasiMail extends Mailable
{
    public function __construct(public string $kode, public string $nama, public int $menit) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Kode verifikasi CampusLink: ' . $this->kode);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.kode-verifikasi');
    }
}

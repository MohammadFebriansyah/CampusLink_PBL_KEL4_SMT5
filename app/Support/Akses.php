<?php

namespace App\Support;

use App\Models\User;

/** Aturan akses yang dipakai bersama oleh banyak tampilan, supaya tidak ditulis ulang di tiap berkas. */
class Akses
{
    /**
     * Direktori hanya untuk mahasiswa dan dosen (sama dengan route direktori.*).
     * Tamu masih melihat tautannya agar diarahkan ke halaman masuk; admin dan mitra tidak,
     * karena membukanya menghasilkan 403.
     */
    public static function bolehDirektori(?User $pengguna): bool
    {
        return $pengguna === null
            || in_array($pengguna->role, [User::ROLE_MAHASISWA, User::ROLE_DOSEN], true);
    }
}

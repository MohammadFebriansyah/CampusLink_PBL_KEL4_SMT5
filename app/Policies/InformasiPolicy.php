<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Aturan hak akses untuk informasi magang, lomba, dan beasiswa.
 * Ditemukan otomatis lewat MagangPolicy, LombaPolicy, BeasiswaPolicy (konvensi nama Laravel).
 *
 * - Mitra hanya boleh mengubah/menghapus informasi MILIKNYA sendiri.
 * - Admin boleh semuanya (FA-04 sampai FA-06, dipakai di Bagian 8).
 * - Kelayakan mitra (akun aktif dan sudah diverifikasi) diperiksa di middleware EnsureRole.
 */
abstract class InformasiPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [User::ROLE_MITRA, User::ROLE_ADMIN], true);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Model $informasi): bool
    {
        if ($user->role === User::ROLE_ADMIN) {
            return true;
        }

        return $user->role === User::ROLE_MITRA && (int) $informasi->user_id === (int) $user->id;
    }

    public function delete(User $user, Model $informasi): bool
    {
        return $this->update($user, $informasi);
    }
}

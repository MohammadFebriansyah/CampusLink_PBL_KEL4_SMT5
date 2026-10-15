<?php

namespace App\Support;

use App\Models\Beasiswa;
use App\Models\Lomba;
use App\Models\Magang;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/** Pemetaan kategori -> model, dan cakupan informasi yang ditinjau seorang pengguna. */
class Informasi
{
    public const MODEL = ['magang' => Magang::class, 'lomba' => Lomba::class, 'beasiswa' => Beasiswa::class];

    public const LABEL = ['magang' => 'Magang', 'lomba' => 'Lomba', 'beasiswa' => 'Beasiswa'];

    public static function cari(string $kategori, string|int $id): Magang|Lomba|Beasiswa
    {
        $model = self::MODEL[$kategori] ?? abort(404);

        return $model::findOrFail($id);
    }

    /**
     * Batasi query informasi (dipakai di whereHasMorph) ke yang pendaftarnya ditinjau $user:
     * mitra -> informasi miliknya; admin -> informasi buatan admin (kegiatan kampus).
     */
    /** Jumlah pendaftar berstatus menunggu yang perlu ditinjau $user (mitra atau admin). */
    public static function pendaftarMenunggu(User $user): int
    {
        return \App\Models\Pendaftaran::query()
            ->where('status', 'menunggu')
            ->whereHasMorph('informasi', array_values(self::MODEL), fn(Builder $q) => self::batasiPeninjau($q, $user))
            ->count();
    }

    public static function batasiPeninjau(Builder $query, User $user): Builder
    {
        $query->where('cara_daftar', 'internal');

        return $user->role === User::ROLE_ADMIN
            ? $query->whereIn('user_id', User::where('role', User::ROLE_ADMIN)->select('id'))
            : $query->where('user_id', $user->id);
    }
}

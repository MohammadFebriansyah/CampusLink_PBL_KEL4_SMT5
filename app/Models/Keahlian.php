<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Keahlian extends Model
{
    protected $table = 'keahlian';

    protected $fillable = ['nama'];

    public function pengguna(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pengguna_keahlian', 'keahlian_id', 'user_id');
    }
}

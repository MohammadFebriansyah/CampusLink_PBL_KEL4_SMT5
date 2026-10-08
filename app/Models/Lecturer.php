<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'academic_title',
    'nidn',
    'nip',
    'institution',
    'faculty',
    'department',
    'expertise',
    'is_pddikti_verified',
    'pddikti_verified_at',
])]
class Lecturer extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_pddikti_verified' => 'boolean',
            'pddikti_verified_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the lecturer profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

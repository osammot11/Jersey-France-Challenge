<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeMember extends Model
{
    protected $fillable = [
        'name', 'email', 'role', 'access_token', 'phone', 'date_of_birth',
        'nationality', 'passport_number', 'passport_expiry', 'terms_version',
        'accepted_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'passport_number' => 'encrypted',
            'date_of_birth' => 'date',
            'passport_expiry' => 'date',
            'accepted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(ChallengeTeam::class, 'team_id');
    }
}

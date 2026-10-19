<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChallengeTeam extends Model
{
    protected $fillable = ['name', 'size', 'captain_email', 'access_token'];

    public function members(): HasMany
    {
        return $this->hasMany(ChallengeMember::class, 'team_id');
    }
}

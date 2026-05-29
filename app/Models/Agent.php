<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agent extends Model
{
    protected $fillable = [
        'agent_team_id',
        'name',
        'slug',
        'role',
        'goal',
        'system_prompt',
        'model',
        'temperature',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(AgentTeam::class, 'agent_team_id');
    }
}

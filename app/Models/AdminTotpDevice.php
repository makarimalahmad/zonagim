<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminTotpDevice extends Model
{
    protected $guarded = ['*'];

    protected $hidden = [
        'secret',
        'secret_fingerprint',
        'name_key',
        'last_used_timestep',
    ];

    protected function casts(): array
    {
        return [
            'secret' => 'encrypted',
            'last_used_timestep' => 'integer',
            'last_used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

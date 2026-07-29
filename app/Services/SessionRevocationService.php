<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class SessionRevocationService
{
    public function revokeAll(User $user, ?string $exceptSessionId = null): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', $user->getKey())
            ->when(
                filled($exceptSessionId),
                fn ($query) => $query->where('id', '!=', $exceptSessionId),
            )
            ->delete();
    }
}

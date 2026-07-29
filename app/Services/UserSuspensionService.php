<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class UserSuspensionService
{
    public function suspend(User $target, User $actor, string $reason): void
    {
        $reason = trim($reason);

        if (
            ! Auth::guard('web')->check()
            || ! Auth::guard('web')->user()->is($actor)
            || mb_strlen($reason) < 10
            || mb_strlen($reason) > 500
        ) {
            throw new AuthorizationException('Aksi suspend membutuhkan sesi admin yang sah.');
        }

        Gate::forUser($actor)->authorize('suspend', $target);

        DB::transaction(function () use ($target, $actor, $reason): void {
            $lockedTarget = User::query()->lockForUpdate()->findOrFail($target->getKey());

            if ($lockedTarget->role !== 'user' || $lockedTarget->isSuspended()) {
                throw new RuntimeException('Aksi suspend tidak diizinkan.');
            }

            $lockedTarget->suspended_at = now();
            $lockedTarget->suspended_by = $actor->getKey();
            $lockedTarget->suspension_reason = $reason;
            $lockedTarget->remember_token = Str::random(60);
            $lockedTarget->save();

            $this->revokeDatabaseSessions($lockedTarget);
        });

        $target->refresh();
    }

    public function reactivate(User $target, User $actor, string $password): void
    {
        $this->authorizeReactivation($target, $actor, $password);

        DB::transaction(function () use ($target): void {
            $lockedTarget = User::query()->lockForUpdate()->findOrFail($target->getKey());

            if ($lockedTarget->role !== 'user') {
                throw new RuntimeException('Aksi aktifkan akun tidak diizinkan.');
            }

            $lockedTarget->suspended_at = null;
            $lockedTarget->suspended_by = null;
            $lockedTarget->suspension_reason = null;
            $lockedTarget->remember_token = Str::random(60);
            $lockedTarget->save();

            $this->revokeDatabaseSessions($lockedTarget);
        });

        $target->refresh();
    }

    private function authorizeReactivation(User $target, User $actor, string $password): void
    {
        if (! Auth::guard('web')->check() || ! Auth::guard('web')->user()->is($actor)) {
            throw new AuthorizationException('Aksi aktifkan akun membutuhkan sesi admin yang sah.');
        }

        Gate::forUser($actor)->authorize('reactivate', $target);

        $rateLimitKey = 'user-reactivation:'.$actor->getKey().'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            throw ValidationException::withMessages([
                'password' => 'Terlalu banyak percobaan. Silakan coba kembali nanti.',
            ]);
        }

        if (! Hash::check($password, $actor->password)) {
            RateLimiter::hit($rateLimitKey, 300);

            throw ValidationException::withMessages([
                'password' => 'Kata sandi admin tidak sesuai.',
            ]);
        }

        RateLimiter::clear($rateLimitKey);
    }

    private function revokeDatabaseSessions(User $user): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', $user->getKey())
            ->delete();
    }
}

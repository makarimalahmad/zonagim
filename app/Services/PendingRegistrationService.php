<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class PendingRegistrationService
{
    private const LIFETIME_MINUTES = 10;

    private const MAX_ATTEMPTS = 5;

    public function create(array $attributes): array
    {
        $id = (string) Str::uuid();
        $otp = (string) random_int(100000, 999999);

        $this->put($id, [
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'password' => $attributes['password'],
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(self::LIFETIME_MINUTES)->timestamp,
            'otp_sent_at' => now()->timestamp,
            'attempts' => 0,
            'resend_count' => 0,
        ]);

        if (app()->environment('testing')) {
            Cache::put($this->testingOtpKey($id), $otp, now()->addMinutes(self::LIFETIME_MINUTES));
        }

        return ['id' => $id, 'otp' => $otp];
    }

    public function get(string $id): ?array
    {
        $pending = Cache::get($this->cacheKey($id));

        return is_array($pending) ? $pending : null;
    }

    public function currentOtpForTesting(string $id): ?string
    {
        if (! app()->environment('testing')) {
            return null;
        }

        $otp = Cache::get($this->testingOtpKey($id));

        return is_string($otp) ? $otp : null;
    }

    public function verifyAndConsume(string $id, string $otp): array
    {
        return Cache::lock($this->lockKey($id), 10)->block(3, function () use ($id, $otp): array {
            $pending = $this->get($id);

            if (! $pending || $this->isExpired($pending)) {
                $this->forget($id);

                return ['status' => 'expired', 'pending' => null];
            }

            if ((int) ($pending['attempts'] ?? 0) >= self::MAX_ATTEMPTS) {
                $this->forget($id);

                return ['status' => 'locked', 'pending' => null];
            }

            if (! Hash::check($otp, (string) ($pending['otp_hash'] ?? ''))) {
                $pending['attempts'] = (int) ($pending['attempts'] ?? 0) + 1;

                if ($pending['attempts'] >= self::MAX_ATTEMPTS) {
                    $this->forget($id);

                    return ['status' => 'locked', 'pending' => null];
                }

                $this->put($id, $pending);

                return ['status' => 'invalid', 'pending' => null];
            }

            $this->forget($id);

            return ['status' => 'valid', 'pending' => $pending];
        });
    }

    public function regenerateOtp(string $id): array
    {
        return Cache::lock($this->lockKey($id), 10)->block(3, function () use ($id): array {
            $pending = $this->get($id);

            if (! $pending || $this->isExpired($pending)) {
                $this->forget($id);

                throw new RuntimeException('Sesi verifikasi telah berakhir.');
            }

            $otp = (string) random_int(100000, 999999);
            $pending['otp_hash'] = Hash::make($otp);
            $pending['expires_at'] = now()->addMinutes(self::LIFETIME_MINUTES)->timestamp;
            $pending['otp_sent_at'] = now()->timestamp;
            $pending['attempts'] = 0;
            $pending['resend_count'] = (int) ($pending['resend_count'] ?? 0) + 1;
            $this->put($id, $pending);

            if (app()->environment('testing')) {
                Cache::put($this->testingOtpKey($id), $otp, now()->addMinutes(self::LIFETIME_MINUTES));
            }

            return ['pending' => $pending, 'otp' => $otp];
        });
    }

    public function forget(string $id): void
    {
        Cache::forget($this->cacheKey($id));
        Cache::forget($this->testingOtpKey($id));
    }

    private function put(string $id, array $pending): void
    {
        $expiresAt = (int) ($pending['expires_at'] ?? now()->timestamp);
        $ttl = max(1, $expiresAt - now()->timestamp);

        Cache::put($this->cacheKey($id), $pending, $ttl);
    }

    private function isExpired(array $pending): bool
    {
        return now()->timestamp >= (int) ($pending['expires_at'] ?? 0);
    }

    private function cacheKey(string $id): string
    {
        return 'pending-registration:'.$id;
    }

    private function lockKey(string $id): string
    {
        return 'pending-registration-lock:'.$id;
    }

    private function testingOtpKey(string $id): string
    {
        return 'pending-registration-testing-otp:'.$id;
    }
}

<?php

namespace App\Services;

use App\Models\AdminTotpDevice;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FAQRCode\Google2FA;
use SensitiveParameter;
use Throwable;

class AdminTotpDeviceService
{
    public const MAX_DEVICES = 3;

    public const CODE_WINDOW = 2;

    public const PASSWORD_MAX_ATTEMPTS = 3;

    private const ENROLLMENT_TTL_SECONDS = 600;

    public function __construct(
        private readonly Google2FA $google2FA,
    ) {}

    public function confirmAdditionalDevicePassword(
        User $actor,
        string $enrollmentId,
        #[SensitiveParameter] string $password,
    ): void {
        $this->authorizeActor($actor);
        $processKey = $this->passwordAttemptKey($actor, $enrollmentId);
        $globalKey = 'admin-mfa-device-password-global:'.$actor->getKey();

        if (
            RateLimiter::tooManyAttempts($processKey, self::PASSWORD_MAX_ATTEMPTS)
            || RateLimiter::tooManyAttempts($globalKey, 9)
        ) {
            throw ValidationException::withMessages([
                'password' => 'Batas percobaan tercapai. Ulangi proses pendaftaran perangkat.',
            ]);
        }

        if (! Hash::check($password, $actor->password)) {
            RateLimiter::hit($processKey, 600);
            RateLimiter::hit($globalKey, 900);

            $message = RateLimiter::attempts($processKey) >= self::PASSWORD_MAX_ATTEMPTS
                ? 'Batas percobaan tercapai. Ulangi proses pendaftaran perangkat.'
                : 'Kata sandi admin tidak valid.';

            throw ValidationException::withMessages(['password' => $message]);
        }

        RateLimiter::clear($processKey);
        RateLimiter::clear($globalKey);
    }

    public function passwordAttempts(User $actor, string $enrollmentId): int
    {
        return RateLimiter::attempts($this->passwordAttemptKey($actor, $enrollmentId));
    }

    public function deviceNameExists(User $actor, string $name): bool
    {
        $this->authorizeActor($actor);
        [, $nameKey] = $this->normalizeName($name);

        return $actor->totpDevices()->where('name_key', $nameKey)->exists();
    }

    public function pendingEnrollment(
        User $actor,
        string $name,
        string $secret,
        #[SensitiveParameter] ?string $password = null,
    ): string {
        $this->authorizeActor($actor);
        [$displayName, $nameKey] = $this->normalizeName($name);
        $deviceCount = $actor->totpDevices()->count();

        if ($deviceCount >= self::MAX_DEVICES) {
            throw ValidationException::withMessages([
                'deviceName' => 'Maksimal tiga perangkat autentikator dapat didaftarkan.',
            ]);
        }

        if ($actor->totpDevices()->where('name_key', $nameKey)->exists()) {
            throw ValidationException::withMessages([
                'deviceName' => 'Nama perangkat sudah terpakai. Gunakan nama lain.',
            ]);
        }

        if ($deviceCount > 0 && (! is_string($password) || ! Hash::check($password, $actor->password))) {
            throw ValidationException::withMessages([
                'password' => 'Kata sandi admin tidak valid.',
            ]);
        }

        if (preg_match('/^[A-Z2-7]{16,128}$/', $secret) !== 1) {
            throw ValidationException::withMessages([
                'deviceName' => 'Secret autentikator tidak valid. Silakan mulai ulang.',
            ]);
        }

        return Crypt::encrypt([
            'user_id' => $actor->getKey(),
            'name' => $displayName,
            'name_key' => $nameKey,
            'secret' => $secret,
            'secret_fingerprint' => hash('sha256', $secret),
            'step_up_verified' => $deviceCount > 0,
            'issued_at' => now()->timestamp,
            'nonce' => (string) Str::uuid(),
        ]);
    }

    public function activate(
        User $actor,
        string $encryptedEnrollment,
        int $lastUsedTimestep,
    ): AdminTotpDevice {
        $this->authorizeActor($actor);

        try {
            $pending = Crypt::decrypt($encryptedEnrollment);
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'deviceName' => 'Sesi pendaftaran perangkat tidak valid. Silakan mulai ulang.',
            ]);
        }

        if (
            ! is_array($pending)
            || ! hash_equals((string) $actor->getKey(), (string) ($pending['user_id'] ?? ''))
            || now()->timestamp - (int) ($pending['issued_at'] ?? 0) > self::ENROLLMENT_TTL_SECONDS
            || blank($pending['secret'] ?? null)
        ) {
            throw ValidationException::withMessages([
                'deviceName' => 'Sesi pendaftaran perangkat telah kedaluwarsa. Silakan mulai ulang.',
            ]);
        }

        return DB::transaction(function () use ($actor, $pending, $lastUsedTimestep): AdminTotpDevice {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($actor->getKey());
            $devices = $lockedUser->totpDevices()->orderBy('slot')->lockForUpdate()->get();

            if ($devices->count() >= self::MAX_DEVICES) {
                throw ValidationException::withMessages([
                    'deviceName' => 'Maksimal tiga perangkat autentikator dapat didaftarkan.',
                ]);
            }

            if ($devices->isNotEmpty() && ! ($pending['step_up_verified'] ?? false)) {
                throw ValidationException::withMessages([
                    'verificationCode' => 'Konfirmasi keamanan wajib dilakukan untuk menambah perangkat.',
                ]);
            }

            $expectedNameKey = hash('sha256', Str::lower((string) ($pending['name'] ?? '')));
            $expectedFingerprint = hash('sha256', (string) ($pending['secret'] ?? ''));

            if (
                ! hash_equals($expectedNameKey, (string) ($pending['name_key'] ?? ''))
                || ! hash_equals($expectedFingerprint, (string) ($pending['secret_fingerprint'] ?? ''))
            ) {
                throw ValidationException::withMessages([
                    'deviceName' => 'Sesi pendaftaran perangkat tidak valid. Silakan mulai ulang.',
                ]);
            }

            if ($devices->contains('name_key', $pending['name_key'])) {
                throw ValidationException::withMessages([
                    'deviceName' => 'Nama perangkat sudah digunakan.',
                ]);
            }

            if ($devices->contains('secret_fingerprint', $pending['secret_fingerprint'])) {
                throw ValidationException::withMessages([
                    'deviceName' => 'Pendaftaran perangkat ini sudah digunakan.',
                ]);
            }

            $usedSlots = $devices->pluck('slot')->all();
            $slot = collect(range(1, self::MAX_DEVICES))
                ->first(fn (int $candidate): bool => ! in_array($candidate, $usedSlots, true));

            $device = new AdminTotpDevice;
            $device->user()->associate($lockedUser);
            $device->slot = $slot;
            $device->name = $pending['name'];
            $device->name_key = $pending['name_key'];
            $device->secret = $pending['secret'];
            $device->secret_fingerprint = $pending['secret_fingerprint'];
            $device->last_used_timestep = $lastUsedTimestep;
            $device->last_used_at = now();
            $device->save();

            return $device;
        }, attempts: 5);
    }

    public function delete(
        User $actor,
        int $deviceId,
        #[SensitiveParameter] string $password,
    ): void {
        $this->authorizeActor($actor);

        DB::transaction(function () use ($actor, $deviceId, $password): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($actor->getKey());

            if (! Hash::check($password, $lockedUser->password)) {
                throw ValidationException::withMessages([
                    'password' => 'Kata sandi admin tidak valid.',
                ]);
            }

            $devices = $lockedUser->totpDevices()->orderBy('id')->lockForUpdate()->get();
            $device = $devices->firstWhere('id', $deviceId);

            if (! $device instanceof AdminTotpDevice) {
                throw (new ModelNotFoundException)->setModel(AdminTotpDevice::class, [$deviceId]);
            }

            if ($devices->count() <= 1) {
                throw ValidationException::withMessages([
                    'device' => 'Perangkat ini tidak dapat dihapus. Minimal satu perangkat autentikator harus tetap aktif.',
                ]);
            }

            $device->delete();
        }, attempts: 5);
    }

    public function verifyAny(User $user, string $code): bool
    {
        if (preg_match('/^\d{6}$/', $code) !== 1) {
            return false;
        }

        return DB::transaction(function () use ($user, $code): bool {
            $lockedUser = User::query()->lockForUpdate()->find($user->getKey());

            if (! $lockedUser instanceof User) {
                return false;
            }

            $devices = $lockedUser->totpDevices()->orderBy('id')->lockForUpdate()->get();
            $timestamp = $this->google2FA->getTimestamp();

            foreach ($devices as $device) {
                try {
                    $secret = $device->secret;
                } catch (DecryptException $exception) {
                    Log::error('Secret perangkat autentikator tidak dapat didekripsi.', [
                        'device_id' => $device->getKey(),
                        'user_id' => $lockedUser->getKey(),
                        'exception' => $exception,
                    ]);

                    continue;
                }

                $matchedTimestamp = $this->google2FA->verifyKey(
                    $secret,
                    $code,
                    self::CODE_WINDOW,
                    $timestamp,
                    0,
                );

                if (! is_int($matchedTimestamp) || $matchedTimestamp <= (int) ($device->last_used_timestep ?? 0)) {
                    continue;
                }

                $device->last_used_timestep = $matchedTimestamp;
                $device->last_used_at = now();
                $device->save();

                return true;
            }

            return false;
        }, attempts: 5);
    }

    private function passwordAttemptKey(User $actor, string $enrollmentId): string
    {
        return 'admin-mfa-device-password:'.$actor->getKey().':'.hash('sha256', $enrollmentId);
    }

    private function authorizeActor(User $actor): void
    {
        if (
            ! Filament::auth()->check()
            || ! Filament::auth()->user()->is($actor)
            || ! $actor->isAdmin()
        ) {
            throw new AuthorizationException('Pengelolaan perangkat autentikator tidak diizinkan.');
        }
    }

    private function normalizeName(string $name): array
    {
        $displayName = Str::of($name)->squish()->toString();

        validator(
            ['deviceName' => $displayName],
            ['deviceName' => ['required', 'string', 'min:2', 'max:64']],
            [
                'deviceName.required' => 'Nama perangkat wajib diisi.',
                'deviceName.min' => 'Nama perangkat minimal 2 karakter.',
                'deviceName.max' => 'Nama perangkat maksimal 64 karakter.',
            ],
        )->validate();

        return [
            $displayName,
            hash('sha256', Str::lower($displayName)),
        ];
    }
}

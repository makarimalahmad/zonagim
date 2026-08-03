<?php

namespace App\Filament\Auth;

use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

class Login extends BaseLogin
{
    private const LOCKOUT_SECONDS = 300;

    private const MAX_FAILED_ATTEMPTS = 3;

    #[Locked]
    public int $lockedUntil = 0;

    public bool $usingRecoveryCode = false;

    public function mount(): void
    {
        parent::mount();

        $this->refreshLockoutState();
    }

    public function authenticate(): ?LoginResponse
    {
        $this->refreshLockoutState();

        if ($this->isLockedOut()) {
            $this->throwLockoutValidationException();
        }

        try {
            $response = parent::authenticate();
        } catch (ValidationException $exception) {
            if ($this->shouldCountFailedAttempt($exception)) {
                RateLimiter::hit($this->loginRateLimitKey(), self::LOCKOUT_SECONDS);
                $this->refreshLockoutState();

                if ($this->isLockedOut()) {
                    $this->throwLockoutValidationException();
                }
            }

            throw $exception;
        }

        if ($response || filled($this->userUndertakingMultiFactorAuthentication)) {
            RateLimiter::clear($this->loginRateLimitKey());
            session()->forget('admin-login-rate-limit-key');
            $this->lockedUntil = 0;
        }

        return $response;
    }

    public function refreshLockoutState(): void
    {
        $key = $this->loginRateLimitKey();

        $this->lockedUntil = RateLimiter::tooManyAttempts($key, self::MAX_FAILED_ATTEMPTS)
            ? now()->addSeconds(RateLimiter::availableIn($key))->timestamp
            : 0;
    }

    public function getLockoutSecondsRemaining(): int
    {
        return max(0, $this->lockedUntil - now()->timestamp);
    }

    public function isLockedOut(): bool
    {
        return $this->getLockoutSecondsRemaining() > 0;
    }

    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()->hidden();
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->disabled(fn (): bool => $this->isLockedOut());
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->disabled(fn (): bool => $this->isLockedOut());
    }

    protected function getAuthenticateFormAction(): Action
    {
        return parent::getAuthenticateFormAction()
            ->disabled(fn (): bool => $this->isLockedOut());
    }

    public function getTitle(): string|Htmlable
    {
        return 'Login Admin';
    }

    public function getHeading(): string|Htmlable|null
    {
        if (blank($this->userUndertakingMultiFactorAuthentication)) {
            return null;
        }

        return $this->usingRecoveryCode
            ? 'Gunakan kode pemulihan'
            : 'Verifikasi tambahan diperlukan';
    }

    public function getSubheading(): string|Htmlable|null
    {
        if (blank($this->userUndertakingMultiFactorAuthentication)) {
            return null;
        }

        return $this->usingRecoveryCode
            ? 'Masukkan salah satu kode pemulihan yang belum pernah digunakan.'
            : 'Akun Anda dilindungi dengan autentikasi multifaktor (MFA). Masukkan kode dari aplikasi autentikator untuk menyelesaikan proses masuk.';
    }

    public function getMultiFactorChallengeFormContentComponent(): Component
    {
        return parent::getMultiFactorChallengeFormContentComponent()
            ->extraAttributes(['class' => 'admin-mfa-challenge']);
    }

    protected function getMultiFactorChallengeFormActions(): array
    {
        return [
            $this->getMultiFactorAuthenticateFormAction()
                ->label('Masuk')
                ->extraAttributes(['class' => 'admin-mfa-submit-action']),
            Action::make('toggleRecoveryCode')
                ->label(fn (): string => $this->usingRecoveryCode
                    ? 'Gunakan kode autentikator'
                    : 'Gunakan kode pemulihan')
                ->button()
                ->outlined()
                ->extraAttributes(['class' => 'admin-mfa-recovery-action'])
                ->action('toggleRecoveryCode'),
            Action::make('useDifferentAccount')
                ->label('Login dengan akun lain')
                ->link()
                ->extraAttributes(['class' => 'admin-mfa-different-account-action'])
                ->url(filament()->getLoginUrl()),
        ];
    }

    public function toggleRecoveryCode(): void
    {
        $this->usingRecoveryCode = ! $this->usingRecoveryCode;
        $this->data['multiFactor']['app'] = [
            'useRecoveryCode' => $this->usingRecoveryCode,
            'code' => null,
            'recoveryCode' => null,
        ];
    }

    private function loginRateLimitKey(): string
    {
        $email = Str::lower(trim((string) ($this->data['email'] ?? '')));

        if (filled($email)) {
            $key = 'admin-login:'.hash('sha256', $email.'|'.request()->ip());
            session()->put('admin-login-rate-limit-key', $key);

            return $key;
        }

        return session()->get(
            'admin-login-rate-limit-key',
            'admin-login:'.hash('sha256', '|'.request()->ip()),
        );
    }

    private function shouldCountFailedAttempt(ValidationException $exception): bool
    {
        return blank($this->userUndertakingMultiFactorAuthentication)
            && array_key_exists('data.email', $exception->errors());
    }

    private function throwLockoutValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => 'Terlalu banyak percobaan gagal. Coba lagi dalam 5 menit.',
        ]);
    }
}

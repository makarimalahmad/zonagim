<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('ai-chat', function (Request $request): array {
            $identity = (string) ($request->user()?->id ?? $request->ip());

            return [
                Limit::perMinute(6)->by('ai-minute:'.$identity),
                Limit::perHour(30)->by('ai-hour:'.$identity),
                Limit::perMinute(120)->by('ai-global'),
            ];
        });

        RateLimiter::for('register', fn (Request $request): array => [
            Limit::perHour(5)->by('register:'.$request->ip()),
            Limit::perDay(20)->by('register-day:'.$request->ip()),
        ]);

        RateLimiter::for('password-reset-link', function (Request $request): array {
            $email = hash('sha256', mb_strtolower((string) $request->input('email')));

            return [
                Limit::perHour(5)->by('reset-ip:'.$request->ip()),
                Limit::perHour(3)->by('reset-email:'.$email),
            ];
        });

        RateLimiter::for('password-reset', fn (Request $request): array => [
            Limit::perHour(10)->by('reset-submit:'.$request->ip()),
        ]);

        RateLimiter::for('otp-verify', fn (Request $request): array => [
            Limit::perMinute(10)->by('otp-ip:'.$request->ip()),
        ]);

        RateLimiter::for('otp-resend', fn (Request $request): array => [
            Limit::perHour(10)->by('otp-resend-ip:'.$request->ip()),
        ]);

        RateLimiter::for('sensitive-auth', fn (Request $request): array => [
            Limit::perMinute(5)->by('sensitive-user:'.($request->user()?->id ?? 'guest')),
            Limit::perMinute(10)->by('sensitive-ip:'.$request->ip()),
        ]);

        RateLimiter::for('profile-regions', fn (Request $request): array => [
            Limit::perMinute(60)->by('regions:'.($request->user()?->id ?? $request->ip())),
        ]);

        Password::defaults(function () {
            return Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised();
        });
    }
}

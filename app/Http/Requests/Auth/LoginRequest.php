<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Services\TurnstileService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'cf_turnstile_response' => ['required', 'string'],
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'cf_turnstile_response.required' => 'Verifikasi keamanan diperlukan. Silakan selesaikan CAPTCHA.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $validator->errors()->has('cf_turnstile_response')) {
                $this->validateTurnstile($validator);
            }
        });
    }

    /**
     * Validate the Turnstile token
     */
    protected function validateTurnstile($validator): void
    {
        $token = $this->input('cf_turnstile_response');

        if (empty($token)) {
            return;
        }

        $turnstile = new TurnstileService;

        if (! $turnstile->verify($token, $this->ip())) {
            $validator->errors()->add(
                'cf_turnstile_response',
                'Verifikasi keamanan gagal. Silakan coba lagi.'
            );
        }
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = User::query()->where('email', $this->string('email'))->first();

        if (
            $user?->isSuspended()
            && Hash::check($this->string('password'), $user->password)
        ) {
            RateLimiter::clear($this->throttleKey());

            throw ValidationException::withMessages([
                'suspended' => 'Akun Anda telah ditangguhkan. Silakan hubungi administrator Zonagim untuk bantuan lebih lanjut.',
            ]);
        }

        if (! Auth::attemptWhen(
            $this->only('email', 'password'),
            fn (User $user): bool => $user->isActiveCustomer(),
            $this->boolean('remember'),
        )) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi yang Anda masukkan tidak sesuai. Silakan periksa kembali.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}

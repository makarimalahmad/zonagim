<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use App\Services\PendingRegistrationService;
use App\Services\TurnstileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(
        Request $request,
        PendingRegistrationService $pendingRegistrations,
    ): RedirectResponse {
        // Validate basic fields
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'cf_turnstile_response' => 'required|string',
        ], [
            'email.unique' => 'Alamat email ini sudah terdaftar. Silakan gunakan email lain atau masuk ke akun Anda.',
            'cf_turnstile_response.required' => 'Verifikasi keamanan diperlukan. Silakan selesaikan CAPTCHA.',
        ]);

        // Validate Turnstile token
        $turnstile = new TurnstileService;
        if (! $turnstile->verify($request->input('cf_turnstile_response'), $request->ip())) {
            throw ValidationException::withMessages([
                'cf_turnstile_response' => 'Verifikasi keamanan gagal. Silakan coba lagi.',
            ]);
        }

        $pending = $pendingRegistrations->create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => Hash::make($request->string('password')->toString()),
        ]);

        RateLimiter::hit('otp-resend:'.$pending['id'], 60);

        try {
            Mail::to($request->string('email')->toString())->send(new OtpMail($pending['otp']));
        } catch (\Throwable) {
            $pendingRegistrations->forget($pending['id']);

            throw ValidationException::withMessages([
                'email' => 'Kode verifikasi belum dapat dikirim. Silakan coba lagi nanti.',
            ]);
        }

        session([
            'auth.verification.pending_id' => $pending['id'],
            'auth.verification.email' => $request->string('email')->toString(),
        ]);

        return redirect()->route('verification.otp');
    }
}

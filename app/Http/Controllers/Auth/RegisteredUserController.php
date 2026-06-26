<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TurnstileService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate basic fields
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'cf_turnstile_response' => 'required|string',
        ], [
            'cf_turnstile_response.required' => 'Verifikasi keamanan diperlukan. Silakan selesaikan CAPTCHA.',
        ]);

        // Validate Turnstile token
        $turnstile = new TurnstileService();
        if (!$turnstile->verify($request->input('cf_turnstile_response'), $request->ip())) {
            throw ValidationException::withMessages([
                'cf_turnstile_response' => 'Verifikasi keamanan gagal. Silakan coba lagi.',
            ]);
        }

        $otp = rand(100000, 999999);
        $ttl = 30 * 60; // 30 minutes

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Hash it now
            'role' => 'user',
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10), // For logic consistency, though cache handles expiry too
            'otp_sent_at' => time(), // Store as Unix timestamp
        ];

        // Store in Cache
        \Illuminate\Support\Facades\Cache::put('pending_registration_' . $request->email, $data, $ttl);

        // Start Rate Limiter for Resend (60 seconds)
        $key = 'otp-resend:' . $request->email;
        \Illuminate\Support\Facades\RateLimiter::hit($key, 60);

        // Kirim Email OTP
        try {
            // Send directly to email address since we don't have a user object yet
            \Illuminate\Support\Facades\Mail::to($request->email)->send(new \App\Mail\OtpMail($otp));
        } catch (\Exception $e) {
            // Log error jika gagal kirim email (opsional)
        }

        // Simpan email di session untuk halaman verifikasi
        session(['auth.verification.email' => $request->email]);

        return redirect()->route('verification.otp');
    }
}

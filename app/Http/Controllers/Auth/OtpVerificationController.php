<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OtpVerificationController extends Controller
{
    public function create()
    {
        $email = session('auth.verification.email');

        if (! $email) {
            return redirect()->route('register');
        }

        // Calculate throttle
        $cacheKey = 'pending_registration_' . $email;
        $cachedData = \Illuminate\Support\Facades\Cache::get($cacheKey);
        $seconds = 0;

        if ($cachedData && isset($cachedData['otp_sent_at'])) {
            $elapsed = time() - $cachedData['otp_sent_at'];
            if ($elapsed < 60) {
                $seconds = 60 - $elapsed;
            }
        } else {
            $key = 'otp-resend:' . $email;
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
        }

        return Inertia::render('Auth/VerifyOtp', [
            'email' => $email,
            'status' => session('status'),
            'throttle' => (int) $seconds,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $cacheKey = 'pending_registration_' . $request->email;
        $cachedData = \Illuminate\Support\Facades\Cache::get($cacheKey);

        // Check if cached data exists
        if (!$cachedData) {
            // Check if user already exists (maybe they verified already or it's a login attempt flow mixed up)
            $existingUser = User::where('email', $request->email)->first();
            if ($existingUser) {
                // If user exists, fall back to DB OTP check (legacy/existing users)
                if ($existingUser->otp_code !== $request->otp) {
                    return back()->withErrors(['otp' => 'Kode OTP salah.']);
                }
                // Verify Email if not verified
                if (! $existingUser->hasVerifiedEmail()) {
                    $existingUser->markEmailAsVerified();
                    event(new \Illuminate\Auth\Events\Verified($existingUser));
                }

                // Clear OTP
                $existingUser->forceFill([
                    'otp_code' => null,
                    'otp_expires_at' => null,
                ])->save();

                Auth::login($existingUser);
                return redirect()->route('market');
            }

            return back()->withErrors(['otp' => 'Sesi verifikasi habis atau tidak ditemukan. Silakan registrasi ulang.']);
        }

        // Verify OTP from Cache
        if ($cachedData['otp_code'] != $request->otp) {
            return back()->withErrors(['otp' => 'Kode OTP salah.']);
        }

        // Create User
        $user = User::create([
            'name' => $cachedData['name'],
            'email' => $cachedData['email'],
            'password' => $cachedData['password'], // Already hashed
            'role' => $cachedData['role'],
            'otp_code' => null, // Clear OTP immediately
            'otp_expires_at' => null,
        ]);

        // Mark Email Verified
        $user->markEmailAsVerified();
        event(new \Illuminate\Auth\Events\Registered($user));
        event(new \Illuminate\Auth\Events\Verified($user));

        // Clear Cache
        \Illuminate\Support\Facades\Cache::forget($cacheKey);

        // Login User
        Auth::login($user);

        return redirect()->route('market');
    }

    public function resend(Request $request)
    {
        $email = session('auth.verification.email');

        if (! $email) {
            return redirect()->route('register');
        }

        $cacheKey = 'pending_registration_' . $email;
        $cachedData = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if ($cachedData) {
            // Logic for pending user (Cache)
            $otp = rand(100000, 999999);

            // Update OTP in Cache
            $cachedData['otp_code'] = $otp;
            $cachedData['otp_expires_at'] = now()->addMinutes(10);
            $cachedData['otp_sent_at'] = time(); // Store as Unix timestamp
            \Illuminate\Support\Facades\Cache::put($cacheKey, $cachedData, 30 * 60);

            try {
                // Determine recipient - assuming OtpMail works with email string or address object
                // If OtpMail STRICTLY needs a User object, we might need a dummy object. 
                // However, Mail::to(string) works.
                \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\OtpMail($otp));
            } catch (\Exception $e) {
                // Log error
            }
            return back()->with('status', 'Kode OTP baru telah dikirim ke email Anda.');
        }

        // Fallback for existing users (Database)
        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('register');
        }

        $otp = rand(100000, 999999);

        $user->forceFill([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ])->save();

        try {
            \Illuminate\Support\Facades\Mail::to($user)->send(new \App\Mail\OtpMail($otp));
        } catch (\Exception $e) {
            // Log error
        }

        return back()->with('status', 'Kode OTP baru telah dikirim ke email Anda.');
    }
}

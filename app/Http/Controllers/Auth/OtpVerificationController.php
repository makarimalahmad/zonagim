<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use App\Services\PendingRegistrationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class OtpVerificationController extends Controller
{
    public function create(
        Request $request,
        PendingRegistrationService $pendingRegistrations,
    ): Response|RedirectResponse {
        $pendingId = $request->session()->get('auth.verification.pending_id');
        $email = $request->session()->get('auth.verification.email');
        $pending = is_string($pendingId) ? $pendingRegistrations->get($pendingId) : null;

        if (! is_string($pendingId) || ! is_string($email) || ! $pending) {
            $this->clearVerificationSession($request);

            return redirect()->route('register');
        }

        return Inertia::render('Auth/VerifyOtp', [
            'email' => $email,
            'status' => session('status'),
            'throttle' => RateLimiter::availableIn('otp-resend:'.$pendingId),
        ]);
    }

    public function store(
        Request $request,
        PendingRegistrationService $pendingRegistrations,
    ): RedirectResponse {
        $validated = $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ]);

        $pendingId = $request->session()->get('auth.verification.pending_id');

        if (! is_string($pendingId)) {
            throw ValidationException::withMessages([
                'otp' => 'Sesi verifikasi telah berakhir. Silakan daftar ulang.',
            ]);
        }

        $verification = $pendingRegistrations->verifyAndConsume($pendingId, $validated['otp']);
        $result = $verification['status'];

        if ($result !== 'valid') {
            if (in_array($result, ['expired', 'locked'], true)) {
                $this->clearVerificationSession($request);
            }

            throw ValidationException::withMessages([
                'otp' => match ($result) {
                    'locked' => 'Terlalu banyak percobaan. Silakan daftar ulang untuk memperoleh kode baru.',
                    'expired' => 'Kode verifikasi telah kedaluwarsa. Silakan daftar ulang.',
                    default => 'Kode verifikasi tidak sesuai.',
                },
            ]);
        }

        $pending = $verification['pending'];

        $user = User::create([
            'name' => $pending['name'],
            'email' => $pending['email'],
            'password' => $pending['password'],
        ]);
        $user->forceFill(['role' => 'user'])->save();
        $user->markEmailAsVerified();

        event(new Registered($user));
        event(new Verified($user));

        $this->clearVerificationSession($request);

        return redirect()->route('login')->with(
            'status',
            'Registrasi berhasil! Akunmu sudah aktif. Silakan login untuk masuk.',
        );
    }

    public function resend(
        Request $request,
        PendingRegistrationService $pendingRegistrations,
    ): RedirectResponse {
        $pendingId = $request->session()->get('auth.verification.pending_id');
        $email = $request->session()->get('auth.verification.email');

        if (! is_string($pendingId) || ! is_string($email)) {
            return redirect()->route('register');
        }

        $cooldownKey = 'otp-resend:'.$pendingId;
        $hourlyKey = 'otp-resend-hour:'.$pendingId;

        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            throw ValidationException::withMessages([
                'otp' => 'Tunggu '.RateLimiter::availableIn($cooldownKey).' detik sebelum mengirim ulang kode.',
            ]);
        }

        if (RateLimiter::tooManyAttempts($hourlyKey, 5)) {
            throw ValidationException::withMessages([
                'otp' => 'Batas pengiriman ulang kode telah tercapai. Silakan coba kembali nanti.',
            ]);
        }

        try {
            $regenerated = $pendingRegistrations->regenerateOtp($pendingId);
            Mail::to($email)->send(new OtpMail($regenerated['otp']));
        } catch (RuntimeException) {
            $this->clearVerificationSession($request);

            return redirect()->route('register')->withErrors([
                'email' => 'Sesi verifikasi telah berakhir. Silakan daftar ulang.',
            ]);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'otp' => 'Kode verifikasi belum dapat dikirim. Silakan coba lagi nanti.',
            ]);
        }

        RateLimiter::hit($cooldownKey, 60);
        RateLimiter::hit($hourlyKey, 3600);

        return back()->with('status', 'Kode verifikasi baru telah dikirim ke email Anda.');
    }

    private function clearVerificationSession(Request $request): void
    {
        $request->session()->forget([
            'auth.verification.pending_id',
            'auth.verification.email',
        ]);
    }
}

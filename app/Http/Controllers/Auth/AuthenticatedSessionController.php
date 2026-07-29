<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): \Symfony\Component\HttpFoundation\Response
    {
        $request->authenticate();

        $request->session()->regenerate();

        $request->session()->flash('success', 'Selamat datang! Kamu berhasil masuk.');

        $target = $this->safeIntendedTarget(
            $request->session()->pull('url.intended'),
        );

        // Full-page visit ke area terautentikasi. Ini memastikan cookie session
        // baru (hasil regenerate) terkirim di request top-level dan menghindari
        // glitch navigasi SPA setelah login (halaman lama "nyangkut" sampai
        // user refresh manual).
        return Inertia::location($target);
    }

    private function safeIntendedTarget(mixed $target): string
    {
        $fallback = route('market');

        if (
            ! is_string($target)
            || $target === ''
            || str_contains($target, '\\')
            || preg_match('/[\x00-\x1F\x7F]/', $target) === 1
            || str_starts_with($target, '//')
        ) {
            return $fallback;
        }

        $targetUrl = parse_url($target);
        $fallbackUrl = parse_url($fallback);

        if ($targetUrl === false || $fallbackUrl === false) {
            return $fallback;
        }

        if (isset($targetUrl['host'])) {
            foreach (['scheme', 'host', 'port'] as $part) {
                if (($targetUrl[$part] ?? null) !== ($fallbackUrl[$part] ?? null)) {
                    return $fallback;
                }
            }
        } elseif (! str_starts_with($target, '/')) {
            return $fallback;
        }

        $path = $targetUrl['path'] ?? '/';

        return str_starts_with($path, '/admin') ? $fallback : $target;
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

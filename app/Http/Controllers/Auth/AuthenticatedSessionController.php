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
            'status' => session('status'),
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

        $target = $request->session()->pull('url.intended', route('market'));

        // User biasa tidak boleh diarahkan ke panel admin.
        if (str_contains($target, '/admin')) {
            $target = route('market');
        }

        // Full-page visit ke area terautentikasi. Ini memastikan cookie session
        // baru (hasil regenerate) terkirim di request top-level dan menghindari
        // glitch navigasi SPA setelah login (halaman lama "nyangkut" sampai
        // user refresh manual).
        return Inertia::location($target);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Hanya logout guard 'web' (user). JANGAN invalidate seluruh session,
        // karena panel admin memakai guard 'admin' yang berbagi cookie session
        // yang sama — meng-invalidate akan ikut me-logout admin & memicu
        // "page expired" (CSRF) di tab admin.
        Auth::guard('web')->logout();

        return redirect('/');
    }
}

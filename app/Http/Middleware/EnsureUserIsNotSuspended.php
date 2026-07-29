<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isSuspended()) {
            return $next($request);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Akun tidak dapat mengakses layanan.'], 401);
        }

        return redirect()->route('login')->withErrors([
            'email' => 'Akun tidak dapat mengakses layanan. Hubungi dukungan jika membutuhkan bantuan.',
        ]);
    }
}

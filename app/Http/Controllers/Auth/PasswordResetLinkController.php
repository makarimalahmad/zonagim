<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::query()
            ->where('email', $request->string('email'))
            ->where('role', 'user')
            ->first();

        if ($user) {
            Password::sendResetLink(['email' => $user->email]);
        }

        return back()->with(
            'status',
            'Jika email terdaftar sebagai akun pengguna, tautan reset telah dikirim.',
        );
    }
}

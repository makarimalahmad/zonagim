<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SessionRevocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(
        Request $request,
        SessionRevocationService $sessions,
    ): RedirectResponse {
        $validated = $request->validate(
            [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', Password::defaults(), 'confirmed'],
            ],
            [
                'current_password.required' => 'Kata sandi saat ini wajib diisi.',
                'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
                'password.required' => 'Kata sandi baru wajib diisi.',
                'password.min' => 'Kata sandi baru minimal 8 karakter.',
                'password.mixed' => 'Kata sandi baru harus memiliki huruf besar dan huruf kecil.',
                'password.letters' => 'Kata sandi baru harus memiliki huruf.',
                'password.numbers' => 'Kata sandi baru harus memiliki angka.',
                'password.symbols' => 'Kata sandi baru harus memiliki simbol.',
                'password.uncompromised' => 'Kata sandi baru pernah terdeteksi dalam kebocoran data. Gunakan kata sandi lain.',
                'password.confirmed' => 'Konfirmasi kata sandi baru tidak sesuai.',
            ],
        );

        $request->user()->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        $sessions->revokeAll($request->user(), $request->session()->getId());

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }
}

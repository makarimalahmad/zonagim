<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Laravolt\Indonesia\Models\Kabupaten;
use Laravolt\Indonesia\Models\Kecamatan;
use Laravolt\Indonesia\Models\Kelurahan;
use Laravolt\Indonesia\Models\Provinsi;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    public function regions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:provinces,cities,districts,villages'],
            'parent' => ['nullable', 'string', 'max:20'],
        ]);

        $regions = match ($validated['type']) {
            'provinces' => Provinsi::query(),
            'cities' => Kabupaten::query()->where('province_code', $validated['parent'] ?? ''),
            'districts' => Kecamatan::query()->where('city_code', $validated['parent'] ?? ''),
            'villages' => Kelurahan::query()->where('district_code', $validated['parent'] ?? ''),
        };

        return response()->json($regions
            ->orderBy('name')
            ->get(['code', 'name']));
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->safe()->except('email'));
        $request->user()->save();

        return Redirect::route('profile.edit')
            ->with('success', 'Informasi profil berhasil diperbarui.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => fn (): ?array => $request->user()?->only([
                    'id',
                    'name',
                    'email',
                    'phone',
                    'address',
                    'created_at',
                ]),
            ],
            // Site key Turnstile diambil dari config (env), bukan di-hardcode di frontend.
            'turnstileSiteKey' => config('services.turnstile.site_key'),
            // Flash message untuk ditampilkan sebagai toast (notif) di frontend.
            'flash' => [
                'id' => fn () => $request->session()->has('status') || $request->session()->has('success') || $request->session()->has('error')
                    ? (string) Str::uuid()
                    : null,
                'status' => fn () => $request->session()->get('status'),
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}

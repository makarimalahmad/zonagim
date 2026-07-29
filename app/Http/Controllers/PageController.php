<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    /**
     * Landing page (guest). User yang sudah login diarahkan ke market.
     */
    public function landing()
    {
        if (Auth::check()) {
            return redirect()->route('market');
        }

        // Logo game dibaca otomatis dari folder public/images/games.
        // Cukup taruh file logo (svg/png/webp) di sana, otomatis tampil di wall hero.
        $dir = public_path('images/games');
        $files = [];
        foreach (['svg', 'png', 'webp', 'jpg', 'jpeg'] as $ext) {
            $files = array_merge($files, glob($dir.'/*.'.$ext) ?: []);
        }
        sort($files);

        $gameLogos = array_map(function ($file) {
            $base = pathinfo($file, PATHINFO_FILENAME);

            return [
                'name' => ucwords(str_replace(['-', '_'], ' ', $base)),
                'src' => asset('images/games/'.basename($file)),
            ];
        }, $files);

        return Inertia::render('Landing', [
            'gameLogos' => array_values($gameLogos),
            'contactUrl' => filled(config('seo.whatsapp'))
                ? 'https://wa.me/'.config('seo.whatsapp')
                : null,
        ]);
    }

    public function terms(): Response
    {
        return Inertia::render('TermsOfService');
    }

    public function privacy(): Response
    {
        return Inertia::render('Privacy');
    }
}

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

        return Inertia::render('Landing', [
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

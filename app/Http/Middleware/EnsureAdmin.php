<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Filament::auth()->user();

        abort_unless(
            $user?->isAdmin() === true
                && Filament::getCurrentPanel()?->getId() === 'admin',
            403,
        );

        return $next($request);
    }
}

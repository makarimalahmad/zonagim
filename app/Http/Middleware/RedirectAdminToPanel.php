<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAdminToPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isAdmin() === true) {
            return redirect()->route('filament.admin.pages.dashboard');
        }

        return $next($request);
    }
}

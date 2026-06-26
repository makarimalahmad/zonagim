<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getHeading(): string|Htmlable
    {
        return 'Sign In';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Welcome back! Please login to continue.';
    }
}

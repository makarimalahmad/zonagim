<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file stores credentials for the external services used by this
    | application.
    |
    */

    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
        'hostname' => env('TURNSTILE_HOSTNAME'),
        // Bisa dimatikan untuk testing/local (default tetap aktif di produksi).
        'enabled' => env('TURNSTILE_ENABLED', true),
    ],

    'groq' => [
        'key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'openai/gpt-oss-120b'),
        'url' => env('GROQ_URL', 'https://api.groq.com/openai/v1/chat/completions'),
    ],

];

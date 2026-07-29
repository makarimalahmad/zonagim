<?php

use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SEO
|--------------------------------------------------------------------------
*/
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

/*
|--------------------------------------------------------------------------
| LANDING & HALAMAN STATIS
|--------------------------------------------------------------------------
*/
Route::middleware('frontend')->group(function () {
    Route::get('/', [PageController::class, 'landing'])->name('landing');
    Route::get('/terms-of-service', [PageController::class, 'terms'])->name('terms');
    Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy');
});

// AI CHATBOT ROUTE
Route::post('/ai/chat', [ChatBotController::class, 'chat'])
    ->middleware('throttle:ai-chat')
    ->name('ai.chat');

/*
|--------------------------------------------------------------------------
| GUEST MARKETPLACE (Bisa diakses tanpa login)
|--------------------------------------------------------------------------
*/

// MARKET LIST - Guest bisa akses
Route::middleware('frontend')->group(function () {
    Route::get('/market', [MarketController::class, 'index'])
        ->name('market');

    // LIST PRODUK PER GAME - Guest bisa akses
    Route::get('/market/{category:slug}', [MarketController::class, 'game'])
        ->name('market.category');
});

/*
|--------------------------------------------------------------------------
| USER MARKETPLACE (Perlu login)
|--------------------------------------------------------------------------
*/
Route::middleware(['frontend', 'auth', 'customer'])->group(function () {

    // DETAIL AKUN (PAKAI MODEL BINDING) - Harus login
    Route::get('/market/{category:slug}/akun/{product:slug}', [MarketController::class, 'show'])
        ->scopeBindings()
        ->name('akun.show');
});

/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/
Route::middleware(['frontend', 'auth', 'customer'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::get('/profile/regions', [ProfileController::class, 'regions'])
        ->middleware('throttle:profile-regions')
        ->name('profile.regions');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->middleware('throttle:sensitive-auth')
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
Route::get('/', [PageController::class, 'landing'])->name('landing');
Route::get('/terms-of-service', [PageController::class, 'terms'])->name('terms');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy');

// AI CHATBOT ROUTE
Route::post('/ai/chat', [App\Http\Controllers\ChatBotController::class, 'chat'])
    ->name('ai.chat');

/*
|--------------------------------------------------------------------------
| GUEST MARKETPLACE (Bisa diakses tanpa login)
|--------------------------------------------------------------------------
*/

// MARKET LIST - Guest bisa akses
Route::get('/market', [MarketController::class, 'index'])
    ->name('market');

// LIST PRODUK PER GAME - Guest bisa akses
Route::get('/market/{category:slug}', [MarketController::class, 'game'])
    ->name('market.category');


/*
|--------------------------------------------------------------------------
| USER MARKETPLACE (Perlu login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // DETAIL AKUN (PAKAI MODEL BINDING) - Harus login
    Route::get('/market/{category:slug}/akun/{product}', [MarketController::class, 'show'])
        ->name('akun.show');
});


/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';

<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanySettingController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // ── Settings ─────────────────────────────────────────────────────────────
    Route::get('/settings',  [CompanySettingController::class, 'index'])->name('settings');
    Route::post('/settings', [CompanySettingController::class, 'store'])->name('settings.store');

    // ── Reviews Inertia page ─────────────────────────────────────────────────
    Route::get('/reviews', [CompanySettingController::class, 'reviewsPage'])->name('reviews');

    // ── Reviews JSON data (axios, throttled: 30 req/min) ─────────────────────
    Route::get('/reviews/data', [CompanySettingController::class, 'reviewsData'])
         ->middleware('throttle:30,1')
         ->name('reviews.data');

    // ── Manual sync trigger (throttled: 3 req/hour per user) ─────────────────
    Route::post('/reviews/sync', [CompanySettingController::class, 'syncReviews'])
         ->middleware('throttle:3,60')
         ->name('reviews.sync');
});

require __DIR__ . '/auth.php';
<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('welcome');
});

Route::get('/welcome', function () {
    return Inertia::render('Welcome');
})->name('welcome');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Games routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/games/match-madness', [App\Http\Controllers\GameController::class, 'matchMadness'])
        ->name('games.match-madness');
    Route::get('/games/match-madness/count', [App\Http\Controllers\GameController::class, 'matchMadnessCount'])
        ->name('games.match-madness.count');
});

require __DIR__.'/settings.php';
require __DIR__.'/vocabulary.php';
require __DIR__.'/admin.php';
require __DIR__.'/testing.php';
require __DIR__.'/auth.php';

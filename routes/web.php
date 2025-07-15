<?php

use App\Http\Controllers\EchoController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard - shows all echo requests
Route::get('/dashboard', [EchoController::class, 'index'])->name('dashboard');

// 3DS Method callback - needs special handling
Route::any('/echo/3ds-method-notification', [EchoController::class, 'threeDSMethodCallback']);

// Echo endpoints - publicly accessible
Route::any('/echo/{path}', [EchoController::class, 'store'])->where('path', '.*');
Route::any('/webhook/{path}', [EchoController::class, 'store'])->where('path', '.*');
Route::any('/api/echo/{path}', [EchoController::class, 'store'])->where('path', '.*');

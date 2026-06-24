<?php

use App\Http\Controllers\SsoController;
use Illuminate\Support\Facades\Route;

Route::prefix('catera')->group(function(){
    Route::get('/sso/verify', [SsoController::class, 'verify'])->name('sso.verify');
    Route::post('/logout', [SsoController::class, 'destroy'])->name('logout.app');

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::livewire('dashboard', 'pages::dashboard.index')->name('dashboard');
        Route::livewire('authorized', 'pages::authorized.index')->name('authorized.index');
        Route::livewire('quota-schedules', 'pages::quota_schedule.index')->name('quota_schedules.index');
    });
});

require __DIR__.'/settings.php';

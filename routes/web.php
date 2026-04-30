<?php

use App\Http\Controllers\SsoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/sso/verify', [SsoController::class, 'verify'])->name('sso.verify');
Route::post('/logout', [SsoController::class, 'destroy'])->name('logout.app');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard.index')->name('dashboard');
    Route::livewire('authorized', 'pages::authorized.index')->name('authorized.index');
    Route::livewire('unauthorized', 'pages::unauthorized.index')->name('unauthorized.index');
    Route::livewire('registereds', 'pages::registered.index')->name('registereds.index');
});

require __DIR__.'/settings.php';

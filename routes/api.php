<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\PermissionRegistrar;

Route::post('/webhook/clear-permission-cache', function (Request $request) {

    if ($request->header('X-Secret-Token') !== config('services.webhook.secret')) {
        Log::info('Token tidak valid.');
        abort(403, 'Akses Ditolak: Token tidak valid.');
    }

    app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

    Log::info('valdi sudah token');

    return response()->json([
        'status' => 'success',
        'message' => 'Cache permission berhasil dibersihkan.'
    ]);
});

Route::get('/test', function () {
    return response()->json([
        'message' => 'Connection successful'
    ]);
});

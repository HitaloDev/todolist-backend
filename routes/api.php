<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Api funcionando!',
        'timestamp' => now()->toIso8601String(),
        'database' => [
            'connected' => true,
            'driver' => config('database.default')
        ]
    ]);
});

Route::middleware('api')->get('/user', function (Request $request) {
    return $request->user();
});


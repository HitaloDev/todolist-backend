<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('tasks')->group(function () {
    Route::get('/statistics', [TaskController::class, 'statistics']);
    Route::get('/overdue', [TaskController::class, 'overdue']);
    Route::get('/status/{status}', [TaskController::class, 'byStatus']);
    Route::get('/priority/{priority}', [TaskController::class, 'byPriority']);
    Route::patch('/{id}/complete', [TaskController::class, 'complete']);
});

Route::apiResource('tasks', TaskController::class);
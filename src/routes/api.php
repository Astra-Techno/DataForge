<?php

use AstraTech\DataForge\Controllers\RequestController;

use Illuminate\Support\Facades\Route;

// Apply authentication middleware to these routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('list/{controller}/{method}', [RequestController::class, 'list']);
    Route::get('all/{controller}/{method}', [RequestController::class, 'all']);
    Route::get('item/{controller}/{method}', [RequestController::class, 'item']);
    Route::get('task/{controller}/{method}', [RequestController::class, 'task']);
    Route::post('task/{controller}/{method}', [RequestController::class, 'task']);
});


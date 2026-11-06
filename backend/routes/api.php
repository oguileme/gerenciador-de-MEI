<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MeiController;
use App\Models\Mei;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/meis', [MeiController::class, 'index'])->can('viewAny', Mei::class);
    Route::post('/meis', [MeiController::class, 'store'])->can('create', Mei::class);
    Route::get('/meis/{mei}', [MeiController::class, 'show'])->can('view', 'mei');
    Route::match(['put', 'patch'], '/meis/{mei}', [MeiController::class, 'update'])->can('update', 'mei');
    Route::delete('/meis/{mei}', [MeiController::class, 'destroy'])->can('delete', 'mei');
    Route::post('/meis/{mei}/restore', [MeiController::class, 'restore'])
        ->withTrashed()
        ->can('restore', 'mei');
});

<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceImportController;
use App\Http\Controllers\MeiController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\QuotePdfController;
use App\Models\Mei;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/meis', [MeiController::class, 'index'])->can('viewAny', Mei::class);
    Route::post('/meis', [MeiController::class, 'store'])->can('create', Mei::class);
    Route::get('/meis/{mei}', [MeiController::class, 'show'])->can('view', 'mei');
    Route::match(['put', 'patch'], '/meis/{mei}', [MeiController::class, 'update'])->can('update', 'mei');
    Route::get('/meis/{mei}/dashboard', [DashboardController::class, 'show'])->can('view', 'mei');
    Route::put('/meis/{mei}/annual-limit', [DashboardController::class, 'updateLimit'])->can('update', 'mei');
    Route::get('/meis/{mei}/invoices', [InvoiceController::class, 'index'])->can('view', 'mei');
    Route::patch('/meis/{mei}/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])
        ->can('update', 'mei');
    Route::post('/meis/{mei}/invoices/import', InvoiceImportController::class)
        ->can('importInvoice', 'mei');
    Route::get('/meis/{mei}/quotes', [QuoteController::class, 'index'])->can('view', 'mei');
    Route::post('/meis/{mei}/quotes', [QuoteController::class, 'store'])->can('update', 'mei');
    Route::get('/meis/{mei}/quotes/{quote}', [QuoteController::class, 'show'])->can('view', 'mei');
    Route::put('/meis/{mei}/quotes/{quote}', [QuoteController::class, 'update'])->can('update', 'mei');
    Route::patch('/meis/{mei}/quotes/{quote}/status', [QuoteController::class, 'updateStatus'])
        ->can('update', 'mei');
    Route::delete('/meis/{mei}/quotes/{quote}', [QuoteController::class, 'destroy'])->can('update', 'mei');
    Route::get('/meis/{mei}/quotes/{quote}/pdf', QuotePdfController::class)->can('view', 'mei');
    Route::delete('/meis/{mei}', [MeiController::class, 'destroy'])->can('delete', 'mei');
    Route::post('/meis/{mei}/restore', [MeiController::class, 'restore'])
        ->withTrashed()
        ->can('restore', 'mei');
});

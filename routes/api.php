<?php

use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\SavingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::prefix('transactions')->group(function () {
        Route::get('/evaluation', [TransactionController::class, 'evaluation']);
        Route::get('/monthly', [TransactionController::class, 'monthly']);
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/', [TransactionController::class, 'store']);
        Route::get('/{id}', [TransactionController::class, 'show']);
        Route::put('/{id}', [TransactionController::class, 'update']);
        Route::delete('/{id}', [TransactionController::class, 'destroy']);
    });

    Route::prefix('savings')->group(function () {
        Route::get('/', [SavingController::class, 'index']);
        Route::get('/monthly', [SavingController::class, 'showByMonth']);
        Route::post('/', [SavingController::class, 'store']);
        Route::patch('/{id}', [SavingController::class, 'update']);
    });
});

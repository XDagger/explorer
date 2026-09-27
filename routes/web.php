<?php

declare(strict_types=1);

use App\Http\Controllers\ApiDocsController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MiningCalculatorController;
use Illuminate\Support\Facades\Route;

Route::middleware('node.synchronized')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('block/{id}', [BlockController::class, 'index'])->name('block')->where('id', '.+');
    Route::get('balance', [BlockController::class, 'balance'])->name('balance');

    Route::get('mining-calculator', [MiningCalculatorController::class, 'index'])->name('mining calculator');
    Route::get('api-docs', [ApiDocsController::class, 'index'])->name('api docs');
});

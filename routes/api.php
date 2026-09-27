<?php

declare(strict_types=1);

use App\Http\Controllers\Api\BlockController;
use App\Http\Controllers\Api\MainBlocksController;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Api\SupplyController;
use Illuminate\Support\Facades\Route;

Route::get('status', [StatusController::class, 'index']);

Route::middleware('node.synchronized')->group(function () {
    Route::get('supply', [SupplyController::class, 'index']);
    Route::get('supply/raw', [SupplyController::class, 'raw']);
    Route::get('supply/coingecko/with-separators', [SupplyController::class, 'coinGeckoWithSeparators']);
    Route::get('supply/coingecko/without-separators', [SupplyController::class, 'coinGeckoWithoutSeparators']);

    Route::get('total-supply', [SupplyController::class, 'indexTotal']);
    Route::get('total-supply/raw', [SupplyController::class, 'rawTotal']);
    Route::get('total-supply/coingecko/with-separators', [SupplyController::class, 'coinGeckoWithSeparatorsTotal']);
    Route::get('total-supply/coingecko/without-separators', [SupplyController::class, 'coinGeckoWithoutSeparatorsTotal']);

    Route::get('last-blocks', [MainBlocksController::class, 'index']);

    Route::get('block/{id}', [BlockController::class, 'index'])->name('api.block')->where('id', '.+');
    Route::get('balance/{id}', [BlockController::class, 'balance'])->where('id', '.+');
});

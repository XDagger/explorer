<?php

use Illuminate\Support\Facades\Route;

Route::get('status', 'App\Http\Controllers\Api\Network\StatusController@show');

Route::group(['middleware' => 'xdag.synchronized'], function () {
	Route::get('supply', 'App\Http\Controllers\Api\SupplyController@show');
	Route::get('supply/raw', 'App\Http\Controllers\Api\SupplyController@raw');
	Route::get('supply/coingecko/with-separators', 'App\Http\Controllers\Api\SupplyController@coinGeckoWithSeparators');
	Route::get('supply/coingecko/without-separators', 'App\Http\Controllers\Api\SupplyController@coinGeckoWithoutSeparators');

	Route::get('total-supply', 'App\Http\Controllers\Api\SupplyController@showTotal');
	Route::get('total-supply/raw', 'App\Http\Controllers\Api\SupplyController@rawTotal');
	Route::get('total-supply/coingecko/with-separators', 'App\Http\Controllers\Api\SupplyController@coinGeckoWithSeparatorsTotal');
	Route::get('total-supply/coingecko/without-separators', 'App\Http\Controllers\Api\SupplyController@coinGeckoWithoutSeparatorsTotal');

	Route::get('last-blocks', 'App\Http\Controllers\Api\LatestBlocksController@show');

	Route::get('block/{search}', 'App\Http\Controllers\Api\BlockController@show')->name('block')->where('search', '(.*)');

	Route::get('balance/{search}', 'App\Http\Controllers\Api\BalanceCheckerController@getBalance')->where('search', '(.*)');
});

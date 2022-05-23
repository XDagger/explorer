<?php

use Illuminate\Support\Facades\Route;

Route::get('status', 'App\Http\Controllers\Api\Network\StatusController@index');

Route::group(['middleware' => 'node.synchronized'], function () {
	Route::get('supply', 'App\Http\Controllers\Api\SupplyController@index');
	Route::get('supply/raw', 'App\Http\Controllers\Api\SupplyController@raw');
	Route::get('supply/coingecko/with-separators', 'App\Http\Controllers\Api\SupplyController@coinGeckoWithSeparators');
	Route::get('supply/coingecko/without-separators', 'App\Http\Controllers\Api\SupplyController@coinGeckoWithoutSeparators');

	Route::get('total-supply', 'App\Http\Controllers\Api\SupplyController@indexTotal');
	Route::get('total-supply/raw', 'App\Http\Controllers\Api\SupplyController@rawTotal');
	Route::get('total-supply/coingecko/with-separators', 'App\Http\Controllers\Api\SupplyController@coinGeckoWithSeparatorsTotal');
	Route::get('total-supply/coingecko/without-separators', 'App\Http\Controllers\Api\SupplyController@coinGeckoWithoutSeparatorsTotal');

	Route::get('last-blocks', 'App\Http\Controllers\Api\MainBlocksController@index');

	Route::get('block/{id}', 'App\Http\Controllers\Api\BlockController@index')->name('block')->where('id', '.+');
	Route::get('balance/{id}', 'App\Http\Controllers\Api\BlockController@balance')->where('id', '.+');
});

<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'xdag.state'], function () {
	Route::get('status', 'App\Http\Controllers\Api\Network\StatusController@show');
	Route::get('supply', 'App\Http\Controllers\Api\Network\SupplyController@show');
	Route::get('supply/raw', 'App\Http\Controllers\Api\Network\SupplyController@raw');
	Route::get('supply/coingecko/with-separators', 'App\Http\Controllers\Api\Network\SupplyController@coinGeckoWithSeparators');
	Route::get('supply/coingecko/without-separators', 'App\Http\Controllers\Api\Network\SupplyController@coinGeckoWithoutSeparators');
	Route::get('total-supply', 'App\Http\Controllers\Api\Network\SupplyController@showTotal');
	Route::get('total-supply/raw', 'App\Http\Controllers\Api\Network\SupplyController@rawTotal');
	Route::get('total-supply/coingecko/with-separators', 'App\Http\Controllers\Api\Network\SupplyController@coinGeckoWithSeparatorsTotal');
	Route::get('total-supply/coingecko/without-separators', 'App\Http\Controllers\Api\Network\SupplyController@coinGeckoWithoutSeparatorsTotal');
	Route::get('last-blocks', 'App\Http\Controllers\Api\Network\LastBlockController@show');
	Route::get('block/{search}', 'App\Http\Controllers\Api\Block\BlockController@show')->name('block')->where('search', '(.*)');
	Route::get('balance/{address}', 'App\Http\Controllers\Api\Wallet\BalanceCheckerController@getBalance')->where('address', '(.*)');
});

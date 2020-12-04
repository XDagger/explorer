<?php

Route::group(['middleware' => 'xdag.state'], function () {
	Route::get('status', 'Network\StatusController@show');
	Route::get('supply', 'Network\SupplyController@show');
	Route::get('supply/raw', 'Network\SupplyController@raw');
	Route::get('supply/coingecko/with-separators', 'Network\SupplyController@coinGeckoWithSeparators');
	Route::get('supply/coingecko/without-separators', 'Network\SupplyController@coinGeckoWithoutSeparators');
	Route::get('total-supply', 'Network\SupplyController@showTotal');
	Route::get('total-supply/raw', 'Network\SupplyController@rawTotal');
	Route::get('total-supply/coingecko/with-separators', 'Network\SupplyController@coinGeckoWithSeparatorsTotal');
	Route::get('total-supply/coingecko/without-separators', 'Network\SupplyController@coinGeckoWithoutSeparatorsTotal');
	Route::get('last-blocks', 'Network\LastBlockController@show');
	Route::get('block/{search}', 'Block\BlockController@show')->name('block')->where('search', '(.*)');
	Route::get('balance/{address}', 'Wallet\BalanceCheckerController@getBalance')->where('address', '(.*)');
});

<?php

Route::group(['middleware' => 'xdag.state'], function () {
	Route::get('status', 'Network\StatusController@show');
	Route::get('supply', 'Network\SupplyController@show');
	Route::get('supply/raw', 'Network\SupplyController@raw');
	Route::get('supply/coingecko/with-separators', 'Network\SupplyController@coinGeckoWithSeparators');
	Route::get('supply/coingecko/without-separators', 'Network\SupplyController@coinGeckoWithoutSeparators');
	Route::get('last-blocks', 'Network\LastBlockController@show');
	Route::get('block/{address_or_hash}', 'Block\BlockController@show')->name('block')->where('address_or_hash', '(.*)');
	Route::get('balance/{address}', 'Wallet\BalanceCheckerController@getBalance')->where('address', '(.*)');
});

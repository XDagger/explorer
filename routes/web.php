<?php

$prefix = starts_with(request()->path(), ['text']) ? 'text' : null;

Route::group(['middleware' => 'xdag.state', 'prefix' => $prefix], function () {
	Route::get('/', 'HomeController@index')->name('home');

	Route::get('block/{address_or_hash}', 'Block\BlockController@show')->name('block')->where('address_or_hash', '(.*)');
	Route::post('block', 'Block\BlockController@search')->name('block search');

	Route::get('mining-calculator', 'Mining\CalculatorController@index')->name('mining calculator');
	Route::post('mining-calculator', 'Mining\CalculatorController@calculate');

	Route::get('balance-checker', 'Wallet\BalanceCheckerController@index')->name('balance checker');
	Route::post('balance-checker', 'Wallet\BalanceCheckerController@getBalance');

	Route::get('node-statistics', 'Node\NodeStatisticsController@index')->name('node statistics');

	Route::get('api-docs', 'ApiDocsController@index')->name('api docs');
});

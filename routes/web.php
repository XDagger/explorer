<?php

use Illuminate\Support\Facades\Route;

$prefix = str_starts_with(request()->path(), 'text') ? 'text' : null;

Route::group(['middleware' => 'xdag.state', 'prefix' => $prefix], function () {
	Route::get('/', 'App\Http\Controllers\HomeController@index')->name('home');

	Route::get('block/{search}', 'App\Http\Controllers\Block\BlockController@show')->name('block')->where('search', '(.*)');
	Route::post('block', 'App\Http\Controllers\Block\BlockController@search')->name('block search');

	Route::get('mining-calculator', 'App\Http\Controllers\Mining\CalculatorController@index')->name('mining calculator');
	Route::post('mining-calculator', 'App\Http\Controllers\Mining\CalculatorController@calculate');

	Route::get('balance-checker', 'App\Http\Controllers\Wallet\BalanceCheckerController@index')->name('balance checker');
	Route::post('balance-checker', 'App\Http\Controllers\Wallet\BalanceCheckerController@getBalance');

	Route::get('node-statistics', 'App\Http\Controllers\Node\NodeStatisticsController@index')->name('node statistics');

	Route::get('api-docs', 'App\Http\Controllers\ApiDocsController@index')->name('api docs');
});

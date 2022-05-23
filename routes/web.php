<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'node.synchronized'], function () {
	Route::get('/', 'App\Http\Controllers\HomeController@index')->name('home');

	Route::get('block/{input}', 'App\Http\Controllers\BlockController@index')->name('block')->where('input', '.+');
	Route::get('balance', 'App\Http\Controllers\BlockController@balance')->name('balance');

	Route::get('mining-calculator', 'App\Http\Controllers\MiningCalculatorController@index')->name('mining calculator');
	Route::get('api-docs', 'App\Http\Controllers\ApiDocsController@index')->name('api docs');
});

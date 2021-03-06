<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Third Party Services
	|--------------------------------------------------------------------------
	|
	| This file is for storing the credentials for third party services such
	| as Stripe, Mailgun, SparkPost and others. This file provides a sane
	| default location for this type of information, allowing packages
	| to have a conventional place to find your various credentials.
	|
	*/

	'mailgun' => [
		'domain' => env('MAILGUN_DOMAIN'),
		'secret' => env('MAILGUN_SECRET'),
	],

	'ses' => [
		'key'	 => env('SES_KEY'),
		'secret' => env('SES_SECRET'),
		'region' => env('SES_REGION', 'us-east-1'),
	],

	'sparkpost' => [
		'secret' => env('SPARKPOST_SECRET'),
	],

	'stripe' => [
		'model'	 => App\Modules\Users\User::class,
		'key'	 => env('STRIPE_KEY'),
		'secret' => env('STRIPE_SECRET'),
	],

	'xdag' => [
		'real' => env('XDAG_USE_REAL_SERVICE', false),
		'socket_file' => env('XDAG_SOCKET_FILE'),
		'whitelist_path' => env('XDAG_WHITELIST_PATH'),
	],
];

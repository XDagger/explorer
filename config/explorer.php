<?php

return [
	'important_ui_message' => env('IMPORTANT_UI_MESSAGE', ''),
	'getblock_binary_name' => env('GETBLOCK_BINARY_NAME', ''),
	'main_blocks_count' => (int) env('MAIN_BLOCKS_COUNT', 20),

	'hashrate_estimation' => [
		'pool_api_url' => $poolApiUrl = (string) env('HASHRATE_ESTIMATION_POOL_API_URL', ''),
		'main_block_remarks' => $mainBlockRemarks = (array) json_decode((string) env('HASHRATE_ESTIMATION_MAIN_BLOCK_REMARKS', '[]'), true),
		'enabled' => $poolApiUrl !== '' && $mainBlockRemarks,
	],
];

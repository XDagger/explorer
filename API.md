# XDAG Block Explorer API

## Synchronizing response

All API requests except `/api/status` check current node status. If the node is currently synchronizing, retry your API request later.

### Error response `HTTP 503`
```json
{
	"error":"synchronizing",
	"message":"Block explorer is currently synchronizing."
}
```

## GET /api/status <a href="/api/status" target="_blank">Try it</a>

Retrieves current node status and information. Data keys `stats.extra_blocks`, `stats.orphan_blocks`, `stats.wait_sync_blocks`, `net_conn.seconds`, `net_conn.in_out_bytes`, `net_conn.in_out_packets` and `net_conn.in_out_dropped` are not present in newest versions of XDAG and should not be used. Timezone is UTC.

### Successful response `HTTP 200`
```json
{
	"version":"0.4.8",
	"state":"Synchronized with the main network. Normal operation.",
	"stats":{
		"hosts":[
			5,
			5
		],
		"blocks":[
			410151028,
			410151028
		],
		"main_blocks":[
			2157765,
			2157765
		],
		"extra_blocks":0,
		"orphan_blocks":0,
		"wait_sync_blocks":0,
		"chain_difficulty":[
			"cdf6de3022a4e49de19791c0be1",
			"cdf6de3022a4e49de19791c0be1"
		],
		"xdag_supply":[
			1183835200,
			1183835200
		],
		"4_hr_hashrate_mhs":[
			1.582601,
			13.513306
		],
		"hashrate":[
			1659477.426176,
			14169728.352256
		]
	},
	"net_conn":[
		{
			"host":"136.243.71.148:13655",
			"seconds":0,
			"in_out_bytes":[
				0,
				0
			],
			"in_out_packets":[
				0,
				0
			],
			"in_out_dropped":[
				0,
				0
			]
		},
		{
			"host":"136.243.55.153:16775",
			"seconds":0,
			"in_out_bytes":[
				0,
				0
			],
			"in_out_packets":[
				0,
				0
			],
			"in_out_dropped":[
				0,
				0
			]
		},
		{
			"host":"136.243.71.148:56230",
			"seconds":0,
			"in_out_bytes":[
				0,
				0
			],
			"in_out_packets":[
				0,
				0
			],
			"in_out_dropped":[
				0,
				0
			]
		},
		{
			"host":"136.243.55.153:48428",
			"seconds":0,
			"in_out_bytes":[
				0,
				0
			],
			"in_out_packets":[
				0,
				0
			],
			"in_out_dropped":[
				0,
				0
			]
		}
	],
	"date":"2022-05-26 20:34:02"
}
```

## GET /api/supply <a href="/api/supply" target="_blank">Try it</a>

Retrieves current supply of XDAG.

### Successful response `HTTP 200`
```json
{
	"supply":1183835200
}
```

## GET /api/supply/raw <a href="/api/supply/raw" target="_blank">Try it</a>

Retrieves current supply of XDAG as `text/plain`.

### Successful response `HTTP 200`
```
1183835200
```

## GET /api/supply/coingecko/with-separators <a href="/api/supply/coingecko/with-separators" target="_blank">Try it</a>

Coingecko specific API, retrieves current supply of XDAG as `text/plain` with formatting separators.

### Successful response `HTTP 200`
```
1,183,835,200.000000000
```

## GET /api/supply/coingecko/without-separators <a href="/api/supply/coingecko/without-separators" target="_blank">Try it</a>

Coingecko specific API, retrieves current supply of XDAG as `text/plain` without any formatting separators.

### Successful response `HTTP 200`
```
232990720000000000
```

## GET /api/total-supply <a href="/api/total-supply" target="_blank">Try it</a>

Retrieves total (maximum) supply of XDAG.

### Successful response `HTTP 200`
```json
{
	"total_supply":1446294144
}
```

## GET /api/total-supply/raw <a href="/api/total-supply/raw" target="_blank">Try it</a>

Retrieves total (maximum) supply of XDAG as `text/plain`.

### Successful response `HTTP 200`
```
1446294144
```

## GET /api/total-supply/coingecko/with-separators <a href="/api/total-supply/coingecko/with-separators" target="_blank">Try it</a>

Coingecko specific API, retrieves total (maximum) supply of XDAG as `text/plain` with formatting separators.

### Successful response `HTTP 200`
```
1,446,294,144.000000000
```

## GET /api/total-supply/coingecko/without-separators <a href="/api/total-supply/coingecko/without-separators" target="_blank">Try it</a>

Coingecko specific API, retrieves total (maximum) supply of XDAG as `text/plain` without any formatting separators.

### Successful response `HTTP 200`
```
1446294144000000000
```

## GET /api/last-blocks <a href="/api/last-blocks" target="_blank">Try it</a>

Retrieves latest 20 main blocks. Timezone is UTC.

### Successful response `HTTP 200`
```json
{
	"limit":20,
	"blocks":[
		{
			"height":"2157777",
			"address":"RoMv6m1bdTwOW7nCCEEzN\/f\/7kOmLRlE",
			"time":"2022-05-26 20:44:47",
			"remark":"..."
		},
		...
	]
}
```

## GET /api/balance/{input} <a href="/api/balance/~LATEST_MAIN_BLOCK_ADDRESS~" target="_blank">Try it</a>

Retrieves balance of any block. Input can be either an address, block hash or main block height.

### Successful response `HTTP 200`
```json
{
	"balance":"24542.435093494"
}
```

### Error response `HTTP 422`
```json
{
	"error":"invalid_input",
	"message":"Incorrect address, block hash or main block height."
}
```

## GET /api/block/{input} <a href="/api/block/~LATEST_MAIN_BLOCK_ADDRESS~" target="_blank">Try it</a>

Retrieves details of any block. Input can be either an address, block hash or main block height. This endpoint accepts the following query parameters, all parameters are optional:

- `addresses_per_page` - integer, number of addresses per page (default `10000000000000`)
- `addresses_page` - integer, addresses page to show (default `1`)
- `addresses_address` - filter address, make sure to URL encode the address properly
- `addresses_date_from` - filter date from, format: `Y-m-d`, for example `2018-05-26`. Always assumes `00:00:00.000` as the time part. Timezone is always UTC. Filter is inclusive.
- `addresses_date_to` - filter date to, format: `Y-m-d`, for example `2018-06-30`. Always assumes `23:59:59.999` as the time part. Timezone is always UTC. Filter is inclusive.
- `addresses_amount_from` - filter amount from
- `addresses_amount_to` - filter amount to
- `addresses_directions[]` - filter directions, available directions: `input`, `output`, `earning`, `snapshot`. You can specify multiple directions, for example `addresses_directions[]=input&addresses_directions[]=output`
- `addresses_remark` - filter remark, any part of the remark string can be specified
- `transactions_per_page` - integer, number of transactions per page (default `10000000000000`)
- `transactions_page` - integer, transactions page to show (default `1`)
- `transactions_address` - filter address, make sure to URL encode the address properly
- `transactions_amount_from` - filter amount from
- `transactions_amount_to` - filter amount to
- `transactions_directions[]` - filter directions, available options: `fee`, `input`, `output`. You can specify multiple directions, for example `transactions_directions[]=input&transactions_directions[]=output`

If any query parameters don't pass validation, they are ignored. Successful response contains `transactions_pagination` and `addresses_pagination` elements describing the dataset and providing links to various pages in the result set. Successful response will contain all block data unless `*_per_page` parameters are specified, output may be large.

Data keys `file_pos` and `file` are not present in newest versions of XDAG and should not be used. Timezone is UTC.

### Error response `HTTP 422`
```json
{
	"error":"invalid_input",
	"message":"Incorrect address, block hash or main block height."
}
```

### Successful response (Main block or Wallet block) `HTTP 200`
```json
{
	"height":"2157788", // height is only present for main blocks
	"time":"2022-05-26 20:56:31.999",
	"timestamp":"18a3fa5ffff",
	"flags":"9f", // null for snapshot or wallet blocks
	"state":"Main",
	"file_pos":"",
	"file":"",
	"hash":"50ebdf53514bf145ea7d86fd5a60bca24bea384c73b95b6a673a8dcfab9910f1", // null for wallet blocks
	"remark":"...", // null for snapshot or wallet blocks
	"difficulty":"cdf6de302338a33dd3ed8cc3dd7", // null for snapshot or wallet blocks
	"balance_address":"8RCZq8+NOmdqW7lzTDjqS6K8YFr9hn3q",
	"balance":"0.000000013",
	"ui_notifications":[{"type":"info","message":"notification associated with block displayed in UI"},{"type":"success","message":"foo"},{"type":"warning","message":"bar"},{"type":"error","message":"baz"}],
	"block_as_transaction":[
		{
			"direction":"output",
			"address":"hNqpDFP24NfQrGiLqykG0WnyWF4bRTwB",
			"amount":"0.000000000"
		},
		...
		{
			"direction":"fee",
			"address":"8RCZq8+NOmdqW7lzTDjqS6K8YFr9hn3q",
			"amount":"0.000000000"
		}
	],
	"block_as_address":[
		{
			"direction":"output",
			"address":"MoxnmiB1aTqnijjwSWhuuAxD1p4v8m0w",
			"amount":"1.279999999",
			"time":"2022-05-26 21:11:36.680",
			"remark":null
		},
		...
		{
			"direction":"earning",
			"address":"8RCZq8+NOmdqW7lzTDjqS6K8YFr9hn3q",
			"amount":"64.000000000",
			"time":"2022-05-26 20:56:31.999",
			"remark":"..."
		}
	],
	"balances_last_week":{
		"2022-05-20":"0.000000000",
		"2022-05-21":"0.000000000",
		"2022-05-22":"0.000000000",
		"2022-05-23":"0.000000000",
		"2022-05-24":"0.000000000",
		"2022-05-25":"0.000000000",
		"2022-05-26":"0.000000019"
	},
	"earnings_last_week":{
		"2022-05-20":"0.000000000",
		"2022-05-21":"0.000000000",
		"2022-05-22":"0.000000000",
		"2022-05-23":"0.000000000",
		"2022-05-24":"0.000000000",
		"2022-05-25":"0.000000000",
		"2022-05-26":"64.000000000"
	},
	"spendings_last_week":{
		"2022-05-20":"0.000000000",
		"2022-05-21":"0.000000000",
		"2022-05-22":"0.000000000",
		"2022-05-23":"0.000000000",
		"2022-05-24":"0.000000000",
		"2022-05-25":"0.000000000",
		"2022-05-26":"63.999999981"
	},
	"balance_change_last_24_hours":"0.000000019",
	"earnings_change_last_24_hours":"64.000000000",
	"spendings_change_last_24_hours":"63.999999981",
	"total_earnings":"64.000000000",
	"total_spendings":"63.999999981",
	"page_earnings_sum":"64.000000000",
	"page_spendings_sum":"63.999999981",
	"filtered_earnings_sum":"64.000000000",
	"filtered_spendings_sum":"63.999999981",
	"kind":"Main block",
	"transactions_pagination":{
		"current_page":1,
		"last_page":1,
		"total":12,
		"per_page":10000000000000,
		"links":{
			"prev":null,
			"next":null,
			"first":"https:\/\/explorer.xdag.io\/api\/block\/8RCZq8+NOmdqW7lzTDjqS6K8YFr9hn3q?transactions_page=1",
			"last":"https:\/\/explorer.xdag.io\/api\/block\/8RCZq8+NOmdqW7lzTDjqS6K8YFr9hn3q?transactions_page=1"
		}
	},
	"addresses_pagination":{
		"current_page":1,
		"last_page":1,
		"total":13,
		"per_page":10000000000000,
		"links":{
			"prev":null,
			"next":null,
			"first":"https:\/\/explorer.xdag.io\/api\/block\/8RCZq8+NOmdqW7lzTDjqS6K8YFr9hn3q?addresses_page=1",
			"last":"https:\/\/explorer.xdag.io\/api\/block\/8RCZq8+NOmdqW7lzTDjqS6K8YFr9hn3q?addresses_page=1"
		}
	}
}
```

### Successful response (Transaction block) `HTTP 200`
```json
{
	"time":"2018-07-11 16:38:24.504",
	"timestamp":"16d18ca0205",
	"flags":"1c",
	"state":"Accepted",
	"file_pos":"",
	"file":"",
	"hash":"4ee22e5da5b979140ba1eb3058add7faff1d75c757b6b38a3d0887b7ba4e2604",
	"remark":null,
	"difficulty":"6a5a22b4abf81b1ec9679b64dce",
	"balance_address":"BCZOureHCD2Ks7ZXx3Ud//rXrVgw66EL",
	"balance":"0.000000000",
	"ui_notifications":[{"type":"info","message":"notification associated with block displayed in UI"},{"type":"success","message":"foo"},{"type":"warning","message":"bar"},{"type":"error","message":"baz"}],
	"block_as_transaction":[
		{
			"direction":"input",
			"address":"ww3E6ITb8cU96n3dQ1C8GidxkZOWPIyO",
			"amount":"243.493238782"
		},
		...
		{
			"direction":"fee",
			"address":"GejHxhxx5VieyGu2nc3eVH9UUIHWVJkE",
			"amount":"0.000000000"
		}
	],
	"block_as_address":[],
	"total_fee":"0.000000000",
	"total_inputs":"243.493238782",
	"total_outputs":"243.493238782",
	"page_fee_sum":"0.000000000",
	"page_inputs_sum":"243.493238782",
	"page_outputs_sum":"243.493238782",
	"filtered_fee_sum":"0.000000000",
	"filtered_inputs_sum":"243.493238782",
	"filtered_outputs_sum":"243.493238782",
	"kind":"Transaction block",
	"transactions_pagination": {
		"current_page": 1,
		"last_page": 1,
		"total": 13,
		"per_page": 10000000000000,
		"links": {
			"prev": null,
			"next": null,
			"first": "https://explorer.domain/api/block/BCZOureHCD2Ks7ZXx3Ud//rXrVgw66EL?transactions_page=1&transactions_per_page=10000000000000",
			"last": "https://explorer.domain/api/block/BCZOureHCD2Ks7ZXx3Ud//rXrVgw66EL?transactions_page=1&transactions_per_page=10000000000000"
		}
	},
	"addresses_pagination": {
		"current_page": 1,
		"last_page": 1,
		"total": 0,
		"per_page": 10000000000000,
		"links": {
			"prev": null,
			"next": null,
			"first": "https://explorer.domain/api/block/BCZOureHCD2Ks7ZXx3Ud//rXrVgw66EL?addresses_page=1&addresses_per_page=10000000000000",
			"last": "https://explorer.domain/api/block/BCZOureHCD2Ks7ZXx3Ud//rXrVgw66EL?addresses_page=1&addresses_per_page=10000000000000"
		}
	}
}
```

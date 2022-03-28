# XDAG Block Explorer API

---

## Node statistics
You can view nodes listing with uptime statistics at [Node statistics page](/node-statistics).

## Synchronizing error response

All API requests except `/api/status` check current daemon state. If the daemon is currently synchronizing, retry your API request later.

**Response status code:** `503`

```json
{
    "error": "synchronizing",
    "message": "Block explorer is currently synchronizing."
}
```

## GET /api/status
### Successful response
**Response status code:** `200`

```json
{
    "version": "0.4.0",
    "state": "Synchronized with the main network. Normal operation.",
    "stats": {
        "hosts": [456, 456],
        "blocks": [58027510, 58030534],
        "main_blocks": [189734, 189734],
        "orphan_blocks": 133,
        "wait_sync_blocks": 260,
        "chain_difficulty": ["630fcc7af946716ccb8c40865da", "630fcc7af946716ccb8c40865da"],
        "xdag_supply": [194287616, 194287616],
        "4_hr_hashrate_mhs": [2357987.48, 75100856.87],
        "hashrate": [2472529079828.48, 78748956093317.12]
    },
    "net_conn": [
        {
            "host": "1.2.3.4:55555",
            "seconds": 183417,
            "in_out_bytes": [33962388992, 6979446272],
            "in_out_packets": [64507158, 11558944],
            "in_out_dropped": [0, 3]
        },
        {
            "host": "111.11.111.111:123",
            "seconds": 148832,
            "in_out_bytes": [24856093184, 5718993408],
            "in_out_packets": [47044872, 9426490],
            "in_out_dropped": [0, 6]
        },
        {
            "host": "110.110.110.11:33333",
            "seconds": 124050,
            "in_out_bytes": [11723742208, 8924076032],
            "in_out_packets": [21416469, 15970217],
            "in_out_dropped": [0, 1]
        },
        {
            "host": "22.22.133.123:11111",
            "seconds": 118280,
            "in_out_bytes": [6187444224, 16896978944],
            "in_out_packets": [10839877, 31606947],
            "in_out_dropped": [0, 3]
        }
    ],
    "date": "2018-06-21 10:18:47"
}
```

## GET /api/supply
### Successful response
**Response status code:** `200`

```json
{
    "supply": 232990720
}
```

## GET /api/supply/raw
### Successful response
**Response status code:** `200`

```
232990720
```

## GET /api/supply/coingecko/with-separators
### Successful response
**Response status code:** `200`

```
232,990,720.000000000
```

## GET /api/supply/coingecko/without-separators
### Successful response
**Response status code:** `200`

```
232990720000000000
```

## GET /api/total-supply
### Successful response
**Response status code:** `200`

```json
{
    "total_supply": 1412000000
}
```

## GET /api/total-supply/raw
### Successful response
**Response status code:** `200`

```
1412000000
```

## GET /api/total-supply/coingecko/with-separators
### Successful response
**Response status code:** `200`

```
1,412,000,000.000000000
```

## GET /api/total-supply/coingecko/without-separators
### Successful response
**Response status code:** `200`

```
1412000000000000000
```

## GET /api/last-blocks

### Successful response
**Response status code:** `200`
```json
{
    "limit": 20,
    "blocks": [
        {
            "height": "196333",
            "address": "QHul3aEvYN8KHkArCW7xOw1i5uLLGRzN",
            "time": "2018-06-14 19:34:23",
            "remark": "mined by remark"
        },
        ...
    ]
}
```

## GET /api/balance/{address}
### Invalid input error

**Response status code:** `422`

```json
{
    "error": "invalid_input",
    "message": "Incorrect address."
}
```

### Successful response

**Response status code:** `200`
```json
{
    "balance": "24542.435093494"
}
```

## GET /api/block/{search}

Search parameter may be either address, block hash or main block height.

### Pagination and filtering
This endpoint accepts the following query parameters. All parameters are optional.
- `transactions_per_page` - integer, number of transactions per page (default 10000000000000)
- `transactions_page` - integer, transactions page to show (default 1 - first page)
- `addresses_per_page` - integer, number of addresses per page (default 10000000000000)
- `addresses_page` - integer, addresses page to show (default 1 - first page)
- `addresses_address` - filter address, make sure to `urlencode` the address properly
- `addresses_date_from` - filter date from, format: `Y-m-d`, for example `2018-05-26`. Always assumes `00:00:00.000` as the time part. Timezone is always `UTC`. Filter is inclusive.
- `addresses_date_to` - filter date to, format: `Y-m-d`, for example `2018-06-30`. Always assumes `23:59:59.999` as the time part. Timezone is always `UTC`. Filter is inclusive.
- `addresses_amount_from` - filter amount from
- `addresses_amount_to` - filter amount to
- `addresses_directions[]` - filter directions, available directions: `input`, `output`, `earning`. You can specify multiple directions, for example `addresses_directions[]=input&addresses_directions[]=output`
- `addresses_remark` - filter remark, search is performed on words in given string, returns entries that match all given words as substrings anywhere in the remark
- `transactions_address` - filter address, make sure to `urlencode` the address properly
- `transactions_amount_from` - filter amount from
- `transactions_amount_to` - filter amount to
- `transactions_directions[]` - filter directions, available options: `fee`, `input`, `output`. You can specify multiple directions, for example `transactions_directions[]=input&transactions_directions[]=output`

Query parameters can be combined, for example `GET /api/block/{search}?addresses_amount_from=1.03&addresses_directions[]=input&transactions_directions[]=output&transactions_per_page=10&transactions_page=2&addresses_per_page=10&addresses_page=2`. If any query parameters don't pass validation, they are ignored.

Endpoint output always contains `transactions_pagination` and `addresses_pagination` elements describing the dataset, together with links to next, previous, first and last pages of the output.

### Invalid input error

**Response status code:** `422`

```json
{
    "error": "invalid_input",
    "message": "Incorrect address, block hash or height."
}
```

### Successful response
This endpoint will return all block data, it's output may be large. Endpoint returns status code `200` even for blocks that don't exist on the network, but the input (address, hash or height) is syntactically valid.

**Response status code:** `200`

**Response type:** `block not found`

```json
{
	"error": "block_not_found",
	"message": "Block was not found."
}
```

**Response status code:** `200`

**Response type:** `block found` (Main or Wallet block)
- height is only present for main blocks

```json
{
    "height": "196333",
    "time":"2018-06-27 19:27:59.999",
    "timestamp":"16ccf94ffff",
    "flags":"1f",
    "state":"Main",
    "file_pos":"19200",
    "file":"storage/01/7e/72/53.dat",
    "hash":"0000000000001de7c93aaf99caf68f36a49895b20dab06b2e93a2c788659ea19",
    "remark": "",
    "difficulty":"684bc5abbf2e34ce9bd55442320",
    "balance_address":"GepZhngsOumyBqsNspWYpDaP9sqZrzrJ",
    "balance":"10.240000054",
    "block_as_transaction":[
        {
            "direction":"fee",
            "address":"GepZhngsOumyBqsNspWYpDaP9sqZrzrJ",
            "amount":"0.000000000"
        },
        ...
    ],
    "block_as_address":[
        {
            "direction":"output",
            "address":"kT86tjhtGZLAhSzAelGD8Zyme9aF5/4T",
            "amount":"15.829778345",
            "time":"2018-06-27 19:42:56.738",
            "remark: "tx remark"
        },
        {
            "direction":"earning",
            "address":"GepZhngsOumyBqsNspWYpDaP9sqZrzrJ",
            "amount":"1024.000000000",
            "time":"2018-06-27 19:27:59.999",
            "remark: ""
        },
        ...
    ],
    "balances_last_week":[
        {
            "2018-06-21":"0"
        },
        {
            "2018-06-22":"0"
        },
        {
            "2018-06-23":"0"
        },
        {
            "2018-06-24":"0"
        },
        {
            "2018-06-25":"0"
        },
        {
            "2018-06-26":"0"
        },
        {
            "2018-06-27":"1024.000000000"
        }
    ],
    "earnings_last_week":[
        {
            "2018-06-21":"0"
        },
        {
            "2018-06-22":"0"
        },
        {
            "2018-06-23":"0"
        },
        {
            "2018-06-24":"0"
        },
        {
            "2018-06-25":"0"
        },
        {
            "2018-06-26":"0"
        },
        {
            "2018-06-27":"1024.000000000"
        }
    ],
    "spendings_last_week":[
        {
            "2018-06-21":"0"
        },
        {
            "2018-06-22":"0"
        },
        {
            "2018-06-23":"0"
        },
        {
            "2018-06-24":"0"
        },
        {
            "2018-06-25":"0"
        },
        {
            "2018-06-26":"0"
        },
        {
            "2018-06-27":"1013.759999922"
        }
    ],
    "balance_change_last_24_hours":10.240000078,
    "earnings_change_last_24_hours":1024,
    "spendings_change_last_24_hours":1013.759999922,
    "total_earnings":1024.000000000,
    "total_spendings":1013.759999922,
    "page_earnings_sum":1024.000000000,
    "page_spendings_sum":1013.759999922,
    "filtered_earnings_sum":1024.000000000,
    "filtered_spendings_sum":1013.759999922,
    "kind":"Main block",
    "transactions_pagination": {
        "current_page": 1,
        "last_page": 1,
        "total": 13,
        "per_page": 10000000000000,
        "links": {
            "prev": null,
            "next": null,
            "first": "https://explorer.domain/api/block/GepZhngsOumyBqsNspWYpDaP9sqZrzrJ?transactions_page=1",
            "last": "https://explorer.domain/api/block/GepZhngsOumyBqsNspWYpDaP9sqZrzrJ?transactions_page=1"
        }
    },
    "addresses_pagination": {
        "current_page": 1,
        "last_page": 1,
        "total": 429,
        "per_page": 10000000000000,
        "links": {
            "prev": null,
            "next": null,
            "first": "https://explorer.domain/api/block/GepZhngsOumyBqsNspWYpDaP9sqZrzrJ?addresses_page=1",
            "last": "https://explorer.domain/api/block/GepZhngsOumyBqsNspWYpDaP9sqZrzrJ?addresses_page=1"
        }
    }
}
```

**Response status code:** `200`

**Response type:** `block found` (Transaction block)
```json
{
    "time":"2018-07-11 16:38:24.504",
    "timestamp":"16d18ca0205",
    "flags":"1c",
    "state":"Accepted",
    "file_pos":"e00",
    "file":"storage/01/7e/72/53.dat",
    "hash":"4ee22e5da5b979140ba1eb3058add7faff1d75c757b6b38a3d0887b7ba4e2604",
    "remark":"",
    "difficulty":"6a5a22b4abf81b1ec9679b64dce",
    "balance_address":"BCZOureHCD2Ks7ZXx3Ud//rXrVgw66EL",
    "balance":"0.000000000",
    "block_as_transaction":[
        {
            "direction":"fee",
            "address":"GejHxhxx5VieyGu2nc3eVH9UUIHWVJkE",
            "amount":"0.000000000"
        },
        {
            "direction":"input",
            "address":"ww3E6ITb8cU96n3dQ1C8GidxkZOWPIyO",
            "amount":"243.493238782"
        },
        ...
    ],
    "block_as_address":[],
    "total_fee":0.000000000,
    "total_inputs":243.493238782,
    "total_outputs":243.493238777,
    "page_fee_sum":0.000000000,
    "page_inputs_sum":243.493238782,
    "page_outputs_sum":243.493238777,
    "filtered_fee_sum":0.000000000,
    "filtered_inputs_sum":243.493238782,
    "filtered_outputs_sum":243.493238777,
    "kind":"Transaction block",
    "transactions_pagination": {
        "current_page": 1,
        "last_page": 1,
        "total": 13,
        "per_page": 10000000000000,
        "links": {
            "prev": null,
            "next": null,
            "first": "https://explorer.domain/api/block/BCZOureHCD2Ks7ZXx3Ud//rXrVgw66EL?transactions_page=1",
            "last": "https://explorer.domain/api/block/BCZOureHCD2Ks7ZXx3Ud//rXrVgw66EL?transactions_page=1"
        }
    },
    "addresses_pagination": {
        "current_page": 1,
        "last_page": 1,
        "total": 429,
        "per_page": 10000000000000,
        "links": {
            "prev": null,
            "next": null,
            "first": "https://explorer.domain/api/block/BCZOureHCD2Ks7ZXx3Ud//rXrVgw66EL?addresses_page=1",
            "last": "https://explorer.domain/api/block/BCZOureHCD2Ks7ZXx3Ud//rXrVgw66EL?addresses_page=1"
        }
    }
}
```

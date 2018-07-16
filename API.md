# XDAG Block Explorer API

---

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
    "version": "0.2.3",
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

## GET /api/last-blocks

### Successful response
**Response status code:** `200`
```json
{
    "limit": 20,
    "blocks": [
        {
            "address": "QHul3aEvYN8KHkArCW7xOw1i5uLLGRzN",
            "time": "2018-06-14 19:34:23"
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

## GET /api/block/{address_or_hash}
### Invalid input error

**Response status code:** `422`

```json
{
    "error": "invalid_input",
    "message": "Incorrect address or block hash."
}
```

### Successful response
This endpoint will return all block data, it's output may be large. Endpoint returns status code `200` even for blocks that don't exist on the network, but the input (address or hash) is syntactically valid.

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
```json
{
    "time":"2018-06-27 19:27:59.999",
    "timestamp":"16ccf94ffff",
    "flags":"1f",
    "state":"Main",
    "file_pos":"19200",
    "hash":"0000000000001de7c93aaf99caf68f36a49895b20dab06b2e93a2c788659ea19",
    "difficulty":"684bc5abbf2e34ce9bd55442320",
    "balance_address":"GepZhngsOumyBqsNspWYpDaP9sqZrzrJ",
    "balance":"10.240000054",
    "block_as_transaction":[
        {
            "direction":"fee",
            "address":"GepZhngsOumyBqsNspWYpDaP9sqZrzrJ",
            "amount":"0.000000000"
        },
        {
            "direction":"output",
            "address":"dhy1Z5W3QSUHlLpEGaAUlqDWFZ/crCHm",
            "amount":"0.000000000"
        },
        ...
    ],
    "block_as_address":[
        {
            "direction":"earning",
            "address":"GepZhngsOumyBqsNspWYpDaP9sqZrzrJ",
            "amount":"1024.000000000",
            "time":"2018-06-27 19:27:59.999"
        },
        {
            "direction":"output",
            "address":"kT86tjhtGZLAhSzAelGD8Zyme9aF5/4T",
            "amount":"15.829778345",
            "time":"2018-06-27 19:42:56.738"
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
    "kind":"Main block"
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
    "hash":"4ee22e5da5b979140ba1eb3058add7faff1d75c757b6b38a3d0887b7ba4e2604",
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
    "kind":"Transaction block"
}
```

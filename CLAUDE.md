About the app
=============

This is the public block explorer of the XDAG cryptocurrency, open source and run by anyone who wants to. There are no users, no login and no registration.

It is built using Laravel 13, with Pest tests. The frontend is Vue 2 and Tailwind, built by Laravel Mix (`npm run production`).

The app is strictly in English, there is no need to write any translations code. Everything is in UTC: the app time zone, every stored date-time and every date shown.

Web requests are stateless. The `web` middleware group has no cookies, sessions or CSRF protection, see `bootstrap/app.php` - the explorer has nothing to remember about its visitors.

How it works
============

All data comes from an XDAGj node (https://github.com/XDagger/xdagj) over its JSON-RPC interface, see `App\Xdag\Node`. The explorer keeps two kinds of data:

- Network wide state, refreshed every minute by `network:fetch-stats`: the `stats` table (one row per minute, three days kept) and the `main_blocks` table (the latest main blocks). While the node cannot be reached, the command records the latest stats again marked as not synchronized, and `App\Http\Middleware\EnsureSynchronizedNode` then shows the synchronizing page instead of any data, and API routes answer `503`.
- A short lived cache of the blocks and balances people look up: the `blocks`, `block_transactions` and `balances` tables use the MEMORY engine and live for `App\Xdag\Block\Cache::TTL` seconds. `blocks:clear-cache` removes expired entries every minute. Whoever creates the cache row fetches the block, concurrent requests for the same id wait for that row to be filled. A row whose `state` is null is not filled yet, and any failure must remove it again.

Blocks may have many transactions, so `Cache::getBlock()` reads the node's response as a stream and parses it piece by piece. It relies on the order of the fields in the node's response, the `transactions` array coming last. Alternatively the block may be fetched by the external `bin/getblock` binary (`GETBLOCK_BINARY_NAME`), which is optional and not covered by tests.

Things to know about the node:

- A block is looked up by its address, hash, main block height or wallet address. Hashes are 64 lowercase hex characters, block addresses are 32 characters of base64 (derived from the hash), wallet addresses are base58check. See `Database\Factories\XdagIdentifier`.
- For an unknown block, current versions of XDAGj leave `result` out of the response entirely, older versions send `"result":null`. Both must keep working.
- `xdag_getStatus` returns every number as a string, hashrates in Java notation such as `1.2345678E7`.
- Times are Unix timestamps in milliseconds. Convert them with `timestampToCarbon()` in PHP, or `TIMESTAMPADD(MICROSECOND, ms * 1000, '1970-01-01')` in SQL.

The optional hashrate estimation (`HASHRATE_ESTIMATION_*`) replaces the network hashrate reported by the node with one derived from a mining pool's hashrate and its share of the latest main blocks.

Development data
================

`php artisan db:seed-dev` replaces all explorer data with made-up data around the current time (`Database\Seeders\DevelopmentSeeder`), so the explorer can be used without a node: three days of stats, the latest main blocks, and wallets with transfers between them, cached for 30 days. Every block a seeded page links to is seeded too. Keep it that way when changing the seeder, its tests check it. The command refuses to run in production.

The API
=======

The API is documented in `API.md`, which the app also renders as its API docs page. Exchanges, wallets and aggregators such as CoinGecko depend on it: never change the shape or the format of a response without a very good reason, and keep `API.md` in sync with every change.

Amounts and other exact numbers
===============================

Amounts are `DECIMAL(56,9)` columns and come out of the database as strings. Never do arithmetic on them with PHP floats: use bcmath (`bcadd()`, `bcsub()`, `bccomp()` and friends) with a scale of 9, and compare with `bccomp()`, never `==`, `<` or `>`. Format them for display with `number_format()` only at the very end, in a view, and never through a float in the API.

Migrations
==========

The app runs in production on servers of many people. Never edit existing migrations in place, every schema change requires a new migration.

All date-time columns are DATETIME, never TIMESTAMP (TIMESTAMP ends in 2038 and is converted through the MySQL session time zone). New migrations must use `dateTime()` and `datetimes()`, never `timestamp()`, `timestamps()` or `useCurrent()`.

SQL must not depend on the time zone of the MySQL session, which may be anything: never use `NOW()`, `CURDATE()`, `FROM_UNIXTIME()`, `UNIX_TIMESTAMP()` or `CURRENT_TIMESTAMP`. Pass times from PHP as bindings instead.

The default table engine is MyISAM, the cache tables are MEMORY. MEMORY tables return rows in no particular order, always order explicitly when order matters.

Running in production
=====================

The explorer needs PHP ^8.5 with bcmath, MySQL 8, and a cron entry running `php artisan schedule:run` every minute. Every scheduled command uses `withoutOverlapping()`. Queue workers are not needed, the queue connection is `sync`.

Block addresses may contain `//` and may end with `/`, and the block routes read the id from the raw request path. Never add trailing slash redirects or slash merging, in `public/.htaccess` or in the web server configuration (nginx needs `merge_slashes off`, see `README.md`).

Deployments are updated by hand (`git pull`, `composer install`, `php artisan migrate`, `npm ci`, `npm run production`), see `README.md`. Config defaults must keep working for an existing `.env` that lacks newer keys.

Testing and writing tests
=========================

Run the suite with `composer test`. It needs `.env.testing` pointing at a database of its own, as the database is wiped and migrated on every run. Copy `.env.testing.example`, run `php artisan key:generate --env=testing` and fill in the credentials.

The suite runs in parallel on a shared testing database, so each test must create its own rows, test the code, and clean up after itself completely:

- Create rows through the `create*()` methods of `Tests\TestCase`, which remove them again after the test. When the code under test creates a block or a balance itself, register its id with `trackBlock()` or `trackBalance()` first.
- Never hardcode ids, addresses or hashes, generate them with `blockHash()`, `blockAddress()` and `walletAddress()`. Never truncate tables and never expect row counts of whole tables.
- The stats and main blocks tables hold network wide state that the app reads as a whole. `createStat()` and `createMainBlock()` take a lock that makes such tests run one at a time, and within it a test sees only the rows it created. Call `lockNetwork()` directly when a test depends on that state without creating any of it.
- `blocks:clear-cache` removes every expired row, so a test creating expired blocks or balances must call `lockExpiredCache()` first.
- Tests must never reach the network, stray HTTP requests fail. Fake the node with `fakeNode()`, which answers JSON-RPC calls by method name, and build block responses with `nodeBlock()`, `nodeRef()` and `nodeTransaction()`, which keep the field order of XDAGj.
- Tests of pages or endpoints that are not about synchronization call `withoutNodeSynchronization()`, so they need not touch the network wide state.

Code style
==========

Code style is enforced using Laravel Pint. There is a `.githooks/pre-commit` hook that runs Pint and stages its changes into the current commit, enable it in your clone with `git config core.hooksPath .githooks`.

The whole project uses PHP strict types. Indentation is four spaces in every file, never tabs (see `.editorconfig`).

Do not write any comments unless absolutely necessary. The code should be self-explanatory on a best effort basis.

<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Network API
|--------------------------------------------------------------------------
|
| These endpoints report the network wide stats and main blocks. Every test
| holds the network lock, so the rows found here are the ones created here.
|
*/

beforeEach(function () {
    $this->lockNetwork();
});

it('reports the status of the node', function () {
    $this->createStat(['created_at' => now()->subMinute()]);
    $this->createStat([
        'version' => '0.8.3',
        'network_type' => 'mainnet',
        'blocks' => 4100000000,
        'network_blocks' => 4100000123,
        'main_blocks' => 3500000,
        'network_main_blocks' => 3500002,
        'difficulty' => '1a2b',
        'network_difficulty' => '1a2c',
        'supply' => '1234567890.987654321',
        'network_supply' => '1234567891.000000000',
        'hashrate' => 12641974272,
        'network_hashrate' => 25600000000,
        'connections' => [
            ['nodeAddress' => '10.0.0.1:8001', 'connectTime' => 1, 'inBound' => 2, 'outBound' => 3],
            ['nodeAddress' => '10.0.0.2:8001', 'connectTime' => 1, 'inBound' => 0, 'outBound' => 0],
        ],
        'created_at' => '2031-05-10 12:30:00.456',
    ]);

    $this->getJson('/api/status')->assertOk()->assertExactJson([
        'version' => '0.8.3',
        'state' => 'Synchronized with the main network. Normal operation.',
        'stats' => [
            'hosts' => [2, 2],
            'blocks' => [4100000000, 4100000123],
            'main_blocks' => [3500000, 3500002],
            'extra_blocks' => 0,
            'orphan_blocks' => 0,
            'wait_sync_blocks' => 0,
            'chain_difficulty' => ['1a2b', '1a2c'],
            'xdag_supply' => [1234567890, 1234567891],
            '4_hr_hashrate_mhs' => [12056.33, 24414.06],
            'hashrate' => [12641974272, 25600000000],
        ],
        'net_conn' => [
            ['host' => '10.0.0.1:8001', 'seconds' => 0, 'in_out_bytes' => [1024, 1536], 'in_out_packets' => [2, 3], 'in_out_dropped' => [0, 0]],
            ['host' => '10.0.0.2:8001', 'seconds' => 0, 'in_out_bytes' => [0, 0], 'in_out_packets' => [0, 0], 'in_out_dropped' => [0, 0]],
        ],
        'date' => '2031-05-10 12:30:00',
    ]);
});

it('reports a node that is still synchronizing', function () {
    $this->createStat(['synchronized' => false, 'network_type' => 'testnet']);

    $this->getJson('/api/status')->assertOk()->assertJsonPath('state', 'Connected to the test network. Synchronizing.');
});

it('reports that the explorer has no stats yet', function () {
    $this->getJson('/api/status')
        ->assertStatus(503)
        ->assertExactJson(['error' => 'synchronizing', 'message' => 'Block explorer is currently synchronizing.']);
});

it('reports the circulating supply in the formats of the aggregators', function (string $uri, string $body, string $contentType) {
    $this->createStat(['supply' => '1234567890.123456789']);

    $response = $this->get($uri)->assertOk();

    expect($response->getContent())->toBe($body)
        ->and($response->headers->get('Content-Type'))->toStartWith($contentType);
})->with([
    ['/api/supply', '{"supply":1234567890}', 'application/json'],
    ['/api/supply/raw', '1234567890', 'text/plain'],
    ['/api/supply/coingecko/with-separators', '1,234,567,890.123456789', 'text/plain'],
    ['/api/supply/coingecko/without-separators', '1234567890123456789', 'text/plain'],
]);

it('reports the total supply in the formats of the aggregators', function (string $uri, string $body, string $contentType) {
    $this->createStat();

    $response = $this->get($uri)->assertOk();

    expect($response->getContent())->toBe($body)
        ->and($response->headers->get('Content-Type'))->toStartWith($contentType);
})->with([
    ['/api/total-supply', '{"total_supply":1446294144}', 'application/json'],
    ['/api/total-supply/raw', '1446294144', 'text/plain'],
    ['/api/total-supply/coingecko/with-separators', '1,446,294,144.000000000', 'text/plain'],
    ['/api/total-supply/coingecko/without-separators', '1446294144000000000', 'text/plain'],
]);

it('lists the latest main blocks from the oldest', function () {
    $this->createStat();
    $newer = $this->createMainBlock(['height' => 3_500_001, 'remark' => 'pool.example.com', 'created_at' => '2031-05-10 12:30:00.999']);
    $older = $this->createMainBlock(['height' => 3_500_000, 'remark' => null, 'created_at' => '2031-05-10 12:28:56.000']);

    $this->getJson('/api/last-blocks')->assertOk()->assertExactJson([
        'limit' => 2,
        'blocks' => [
            ['height' => '3500000', 'address' => $older->address, 'time' => '2031-05-10 12:28:56', 'remark' => null],
            ['height' => '3500001', 'address' => $newer->address, 'time' => '2031-05-10 12:30:00', 'remark' => 'pool.example.com'],
        ],
    ]);
});

it('limits API clients to sixty requests a minute', function () {
    $this->createStat();

    foreach (range(1, 60) as $request) {
        $this->getJson('/api/status')->assertOk();
    }

    $this->getJson('/api/status')->assertTooManyRequests();
});

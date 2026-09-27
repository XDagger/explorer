<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Node synchronization
|--------------------------------------------------------------------------
|
| Pages show data only while the latest network stats say the node is
| synchronized. These tests hold the network lock, so the stats they create
| are the only ones there are.
|
*/

beforeEach(function () {
    $this->lockNetwork();
});

it('shows the pages while the node is synchronized', function (string $uri) {
    $this->createStat();

    $this->get($uri)->assertOk()->assertDontSee('Synchronization process should be finished shortly');
})->with(['/', '/balance', '/mining-calculator', '/api-docs']);

it('asks to wait while the node is synchronizing', function (string $uri) {
    $this->createStat(['created_at' => now()->subMinute()]);
    $this->createStat(['synchronized' => false]);

    $this->get($uri)->assertOk()->assertSee('Block explorer is currently synchronizing...');
})->with(['/', '/balance', '/mining-calculator', '/api-docs', '/block/1']);

it('asks to wait before the first stats are fetched', function () {
    $this->get('/')->assertOk()->assertSee('Block explorer is currently synchronizing...');
});

it('answers API clients with an error while the node is synchronizing', function (string $uri, array $headers) {
    $this->createStat(['synchronized' => false]);

    $this->get($uri, $headers)
        ->assertStatus(503)
        ->assertExactJson(['error' => 'synchronizing', 'message' => 'Block explorer is currently synchronizing.']);
})->with([
    'API route' => ['/api/supply', []],
    'JSON request of a page' => ['/', ['Accept' => 'application/json']],
]);

it('names the network in the title outside of the main network', function (string $networkType, string $title) {
    $this->createStat(['network_type' => $networkType]);

    $this->get('/')->assertOk()->assertSee("<title>$title</title>", false);
})->with([
    ['mainnet', 'XDAG Block Explorer'],
    ['testnet', 'XDAG Testnet Explorer'],
    ['devnet', 'XDAG Devnet Explorer'],
]);

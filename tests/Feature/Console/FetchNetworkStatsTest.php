<?php

declare(strict_types=1);

use App\Xdag\Block\MainBlock;
use App\Xdag\Network\Stat;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Fetching network stats
|--------------------------------------------------------------------------
|
| The command rewrites the network wide stats and main blocks, so every test
| here holds the network lock: the rows it finds are its own.
|
*/

beforeEach(function () {
    $this->lockNetwork();

    config([
        'xdag.rpc_url' => 'http://node.test:10001',
        'explorer.main_blocks_count' => 20,
        'explorer.hashrate_estimation' => ['pool_api_url' => '', 'main_block_remarks' => [], 'enabled' => false],
    ]);
});

/**
 * @param  array<int, string|null>  $remarks
 * @return array<int, array<string, mixed>>
 */
function nodeMainBlocks(array $remarks): array
{
    return array_map(fn (int $index) => nodeBlock([
        'height' => 3_500_000 - $index,
        'balance' => '64.000000000',
        'blockTime' => 1790000000000 - $index * 64000,
        'state' => 'Main',
        'remark' => $remarks[$index],
        'type' => 'Main',
        'flags' => '1f',
        'refs' => null,
        'transactions' => null,
    ]), array_keys($remarks));
}

/**
 * @param  array<string, mixed>  $methods
 * @param  array<string, mixed>  $otherUrls
 */
function fakeRunningNode(array $methods = [], array $otherUrls = []): void
{
    fakeNode($methods + [
        'xdag_getStatus' => [
            'nblock' => '4100000000',
            'totalNblocks' => '4100000123',
            'nmain' => '3500000',
            'totalNmain' => '3500002',
            'curDiff' => '0x1a2b3c4d5e6f',
            'netDiff' => '0x1a2b3c4d5e70',
            'hashRateOurs' => '1.2345678E7',
            'hashRateTotal' => '2.5E7',
            'ourSupply' => '1234567890.123456789',
            'netSupply' => '1234567891.000000000',
        ],
        'xdag_getBlocksByNumber' => nodeMainBlocks(array_fill(0, 20, '')),
        'xdag_syncing' => ['currentBlock' => 3500000, 'highestBlock' => 3500002, 'isSyncDone' => true],
        'xdag_protocolVersion' => '0.8.3',
        'xdag_netType' => 'mainnet',
        'xdag_getRewardByNumber' => '64.000000000',
        'xdag_netConnectionList' => [['nodeAddress' => '10.0.0.1:8001', 'connectTime' => 1790000000, 'inBound' => 0, 'outBound' => 0]],
    ], $otherUrls);
}

function latestStat(): Stat
{
    return Stat::query()->orderByDesc('id')->firstOrFail();
}

it('stores the stats of the node', function () {
    fakeRunningNode();

    $this->artisan('network:fetch-stats')->expectsOutput('Completed successfully.')->assertSuccessful();

    expect(latestStat()->toArray())->toMatchArray([
        'synchronized' => 1,
        'version' => '0.8.3',
        'network_type' => 'mainnet',
        'blocks' => 4100000000,
        'network_blocks' => 4100000123,
        'main_blocks' => 3500000,
        'network_main_blocks' => 3500002,
        'difficulty' => '1a2b3c4d5e6f',
        'network_difficulty' => '1a2b3c4d5e70',
        'supply' => '1234567890.123456789',
        'network_supply' => '1234567891.000000000',
        'block_reward' => '64.000000000',
        'hashrate' => 12641974272,
        'network_hashrate' => 25600000000,
        'connections' => [['nodeAddress' => '10.0.0.1:8001', 'connectTime' => 1790000000, 'inBound' => 0, 'outBound' => 0]],
    ])->and(latestStat()->created_at->diffInSeconds(now(), true))->toBeLessThan(5);

    Http::assertSent(fn (Request $request) => $request['method'] === 'xdag_getBlocksByNumber' && $request['params'] === [20]);
    Http::assertSent(fn (Request $request) => $request['method'] === 'xdag_getRewardByNumber' && $request['params'] === [3500000]);
});

it('records that the node is still synchronizing', function () {
    fakeRunningNode(['xdag_syncing' => ['currentBlock' => 10, 'highestBlock' => 3500002, 'isSyncDone' => false]]);

    $this->artisan('network:fetch-stats')->assertSuccessful();

    expect(latestStat()->synchronized)->toBe(0);
});

it('stores the latest main blocks', function () {
    $mainBlocks = nodeMainBlocks(['pool.example.com', '', ...array_fill(0, 18, null)]);

    fakeRunningNode(['xdag_getBlocksByNumber' => $mainBlocks]);

    $this->artisan('network:fetch-stats')->assertSuccessful();

    $stored = MainBlock::query()->findOrFail($mainBlocks[0]['address']);

    expect($stored->height)->toBe(3_500_000)
        ->and($stored->balance)->toBe('64.000000000')
        ->and($stored->remark)->toBe('pool.example.com')
        ->and($stored->created_at->format('Y-m-d H:i:s.v'))->toBe('2026-09-21 14:13:20.000')
        ->and(MainBlock::query()->findOrFail($mainBlocks[1]['address'])->remark)->toBeNull()
        ->and(MainBlock::query()->whereIn('address', array_column($mainBlocks, 'address'))->count())->toBe(20);
});

it('updates main blocks it already knows and forgets those no longer among the latest', function () {
    $mainBlocks = nodeMainBlocks(array_fill(0, 20, ''));
    $known = $this->createMainBlock(['address' => $mainBlocks[0]['address'], 'height' => 1, 'balance' => '1.000000000']);
    $outdated = $this->createMainBlock();

    fakeRunningNode(['xdag_getBlocksByNumber' => $mainBlocks]);

    $this->artisan('network:fetch-stats')->assertSuccessful();

    expect($known->fresh())
        ->height->toBe(3_500_000)
        ->balance->toBe('64.000000000')
        ->and($outdated->fresh())->toBeNull();
});

it('removes stats older than three days', function () {
    $old = $this->createStat(['created_at' => now()->subDays(3)->subMinute()]);
    $recent = $this->createStat(['created_at' => now()->subDays(3)->addMinute()]);

    fakeRunningNode();

    $this->artisan('network:fetch-stats')->assertSuccessful();

    expect($old->fresh())->toBeNull()
        ->and($recent->fresh())->not->toBeNull();
});

it('marks the network as not synchronized with the latest known stats when the node is unreachable', function () {
    $this->createStat(['blocks' => 1, 'created_at' => now()->subMinutes(2)]);
    $latest = $this->createStat(['blocks' => 2, 'network_hashrate' => 123456, 'created_at' => now()->subMinute()]);
    $mainBlock = $this->createMainBlock();

    Http::fake(fn () => Http::failedConnection());

    $this->artisan('network:fetch-stats')->expectsOutput('Completed successfully.')->assertSuccessful();

    $stat = latestStat();

    expect($stat->id)->toBeGreaterThan($latest->id)
        ->and($stat->synchronized)->toBe(0)
        ->and($stat->blocks)->toBe(2)
        ->and($stat->network_hashrate)->toBe(123456)
        ->and($stat->connections)->toBe($latest->connections)
        ->and($stat->created_at->diffInSeconds(now(), true))->toBeLessThan(5)
        ->and($mainBlock->fresh())->not->toBeNull();
});

describe('hashrate estimation', function () {
    beforeEach(function () {
        config(['explorer.hashrate_estimation' => [
            'pool_api_url' => 'http://pool.test/api/hashrate',
            'main_block_remarks' => ['pool.test', 'https://pool.test'],
            'enabled' => true,
        ]]);
    });

    function poolMainBlocks(): array
    {
        return nodeMainBlocks(['pool.test', 'other pool', 'https://pool.test', '', 'pool.test', 'pool.test', ...array_fill(0, 14, 'other pool')]);
    }

    it('estimates the network hashrate from the share of main blocks found by the pool', function () {
        fakeRunningNode(
            ['xdag_getBlocksByNumber' => poolMainBlocks()],
            ['pool.test/*' => Http::response(['unit' => 'Gh/s', 'value' => '0.93', 'hashrate' => 1_000_000_000])],
        );

        $this->artisan('network:fetch-stats')->assertSuccessful();

        expect(latestStat()->network_hashrate)->toBe(5_000_000_000)
            ->and(latestStat()->hashrate)->toBe(12641974272);

        Http::assertSent(fn (Request $request) => $request->url() === 'http://pool.test/api/hashrate'
            && $request->method() === 'GET'
            && $request->hasHeader('Accept', 'application/json'));
    });

    it('keeps the hashrate reported by the node when the pool cannot tell', function (Closure $pool) {
        fakeRunningNode(['xdag_getBlocksByNumber' => poolMainBlocks()], ['pool.test/*' => $pool()]);

        $this->artisan('network:fetch-stats')->assertSuccessful();

        expect(latestStat()->network_hashrate)->toBe(25600000000);
    })->with([
        'pool unreachable' => fn () => fn () => Http::failedConnection(),
        'server error page' => fn () => fn () => Http::response('<html>502 Bad Gateway</html>', 502),
        'not JSON' => fn () => fn () => Http::response('hashrate: 5'),
        'hashrate missing' => fn () => fn () => Http::response(['unit' => 'Gh/s']),
        'hashrate as a string' => fn () => fn () => Http::response(['hashrate' => '1000000000']),
        'hashrate as a float' => fn () => fn () => Http::response(['hashrate' => 1234567.5]),
    ]);

    it('does not ask the pool when it found none of the latest main blocks', function () {
        fakeRunningNode([], ['pool.test/*' => Http::response(['hashrate' => 1_000_000_000])]);

        $this->artisan('network:fetch-stats')->assertSuccessful();

        expect(latestStat()->network_hashrate)->toBe(25600000000);

        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), 'pool.test'));
    });

    it('does not ask the pool when estimation is off', function () {
        config(['explorer.hashrate_estimation.enabled' => false]);

        fakeRunningNode(['xdag_getBlocksByNumber' => poolMainBlocks()], ['pool.test/*' => Http::response(['hashrate' => 1_000_000_000])]);

        $this->artisan('network:fetch-stats')->assertSuccessful();

        expect(latestStat()->network_hashrate)->toBe(25600000000);

        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), 'pool.test'));
    });
});

<?php

declare(strict_types=1);

use App\Xdag\Block\Block;
use App\Xdag\Block\MainBlock;
use App\Xdag\Block\Transaction;
use App\Xdag\Network\Stat;
use Database\Seeders\DevelopmentSeeder;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Development data
|--------------------------------------------------------------------------
|
| The command empties the explorer tables before seeding, which a test on
| the shared database must never do. The seeder is tested on its own while
| holding the network lock, and its rows are tracked like any other.
|
*/

function seedDevelopmentData(): DevelopmentSeeder
{
    test()->lockNetwork();
    test()->travelTo('2031-05-10 12:30:00');

    $seeder = new DevelopmentSeeder;
    $seeder->run();

    foreach ($seeder->blockIds() as $id) {
        test()->trackBlock($id);
    }

    foreach ($seeder->notificationIds() as $id) {
        test()->trackNotification($id);
    }

    return $seeder;
}

it('seeds three days of network stats and the latest main blocks up to now', function () {
    seedDevelopmentData();

    $stats = Stat::query()->orderBy('id')->get();
    $mainBlocks = MainBlock::query()->orderByDesc('height')->get();

    expect($stats)->toHaveCount(DevelopmentSeeder::STATS_DAYS * 24 * 60)
        ->and($stats->last()->created_at->format('Y-m-d H:i:s'))->toBe('2031-05-10 12:30:00')
        ->and($stats->first()->created_at->format('Y-m-d H:i:s'))->toBe('2031-05-07 12:31:00')
        ->and($stats->last()->synchronized)->toBe(1)
        ->and($stats->pluck('blocks')->sort()->values()->all())->toBe($stats->pluck('blocks')->all())
        ->and($mainBlocks)->toHaveCount(config('explorer.main_blocks_count'))
        ->and($mainBlocks->first()->height)->toBe($stats->last()->main_blocks)
        ->and($mainBlocks->first()->created_at->format('Y-m-d H:i:s'))->toBe('2031-05-10 12:30:00')
        ->and($mainBlocks[1]->created_at->format('Y-m-d H:i:s'))->toBe('2031-05-10 12:28:56');
});

it('caches every block a seeded page links to, with wallet balances equal to their history', function () {
    $seeder = seedDevelopmentData();

    $linked = Transaction::query()->whereIn('block_id', $seeder->blockIds())->distinct()->pluck('address')
        ->merge(Block::query()->whereKey($seeder->blockIds())->whereNotNull('hash')->pluck('hash'))
        ->merge(MainBlock::query()->pluck('address'))
        ->merge(MainBlock::query()->pluck('height')->map(fn (int $height) => (string) $height))
        ->unique();

    expect($linked->diff(Block::query()->whereKey($linked)->pluck('id'))->values()->all())->toBe([]);

    $wallets = Block::query()->whereKey($seeder->blockIds())->where('type', 'Wallet')->get();

    expect($wallets)->toHaveCount(DevelopmentSeeder::WALLETS);

    foreach ($wallets as $wallet) {
        $history = $wallet->transactions()->where('view', 'wallet')->pluck('amount')->reduce(fn (string $sum, string $amount) => bcadd($sum, $amount, 9), '0');

        expect($history)->toBe($wallet->balance)
            ->and(bccomp($wallet->balance, '0', 9))->toBe(1);
    }
});

it('lets every seeded page be browsed without a node', function () {
    $seeder = seedDevelopmentData();
    $latestHeight = MainBlock::query()->max('height');

    Http::fake();

    $this->get('/')->assertOk()->assertDontSee('Synchronization process should be finished shortly');

    foreach (array_filter($seeder->summary(), fn (string $path) => ! str_ends_with($path, '/')) as $path) {
        $this->get($path)->assertOk();
    }

    $this->get('/block/'.($latestHeight - 199))->assertOk();
    $this->get('/block/'.($latestHeight + 1))->assertRedirect('/?404');
    $this->get('/block/'.($latestHeight - 200))->assertRedirect('/?404');

    Http::assertNothingSent();
});

it('refuses to seed development data in production', function () {
    $block = $this->createBlock();

    $this->app['env'] = 'production';

    $this->artisan('db:seed-dev', ['--force' => true])
        ->expectsOutput('Development data must never be seeded in production.')
        ->assertFailed();

    expect(Block::query()->find($block->id))->not->toBeNull();
});

it('asks before replacing the data', function () {
    $block = $this->createBlock();

    $this->artisan('db:seed-dev')
        ->expectsConfirmation('This deletes all stats, main blocks and cached blocks in the '.DB::connection()->getDatabaseName().' database. Continue?', 'no')
        ->assertFailed();

    expect(Block::query()->find($block->id))->not->toBeNull();
});

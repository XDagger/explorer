<?php

declare(strict_types=1);

use App\Xdag\Block\Balance;
use App\Xdag\Block\Block;
use App\Xdag\Block\Transaction;

beforeEach(function () {
    $this->lockExpiredCache();
});

it('removes expired blocks with their transactions and expired balances', function () {
    $expiredBlock = $this->trackBlock(Block::factory()->expired()->create());
    $expiredTransaction = $this->createTransaction(['block_id' => $expiredBlock->id]);
    $freshBlock = $this->createBlock();
    $freshTransaction = $this->createTransaction(['block_id' => $freshBlock->id]);
    $expiredBalance = $this->trackBalance(Balance::factory()->expired()->create());
    $freshBalance = $this->createBalance();

    $this->artisan('blocks:clear-cache')->expectsOutput('Completed successfully.')->assertSuccessful();

    expect(Block::query()->find($expiredBlock->id))->toBeNull()
        ->and(Transaction::query()->find($expiredTransaction->id))->toBeNull()
        ->and(Balance::query()->find($expiredBalance->id))->toBeNull()
        ->and(Block::query()->find($freshBlock->id))->not->toBeNull()
        ->and(Transaction::query()->find($freshTransaction->id))->not->toBeNull()
        ->and(Balance::query()->find($freshBalance->id))->not->toBeNull();
});

it('removes all expired entries in one run, however many there are', function () {
    $blocks = collect(range(1, 45))->map(fn () => $this->trackBlock(Block::factory()->expired()->create())->id);
    $balances = collect(range(1, 45))->map(fn () => $this->trackBalance(Balance::factory()->expired()->create())->id);

    $this->artisan('blocks:clear-cache')->assertSuccessful();

    expect(Block::query()->whereKey($blocks)->count())->toBe(0)
        ->and(Balance::query()->whereKey($balances)->count())->toBe(0);
});

<?php

declare(strict_types=1);

use App\Xdag\Block\Balance;
use App\Xdag\Block\Block;
use App\Xdag\Block\MainBlock;
use App\Xdag\Block\Notification;
use App\Xdag\Network\Stat;

it('stores blocks with millisecond precision creation times', function () {
    $block = $this->createBlock(['created_at' => '2041-03-04 05:06:07.891']);

    expect(Block::query()->findOrFail($block->id)->created_at->format('Y-m-d H:i:s.v'))->toBe('2041-03-04 05:06:07.891');
});

it('stores the transactions of a block', function () {
    $block = $this->createBlock();
    $input = $this->createTransaction(['block_id' => $block->id, 'amount' => '12.345678901']);
    $output = $this->createTransaction(['block_id' => $block->id, 'direction' => 'output', 'amount' => '-1.000000001']);

    expect($block->transactions()->orderBy('id')->pluck('amount', 'id')->all())->toBe([
        $input->id => '12.345678901',
        $output->id => '-1.000000001',
    ]);
});

it('stores balances and notifications', function () {
    $balance = $this->createBalance(['balance' => '42.000000001']);
    $notification = $this->createNotification(['message' => 'Hello']);

    expect(Balance::query()->findOrFail($balance->id)->balance)->toBe('42.000000001')
        ->and(Notification::query()->findOrFail($notification->id)->message)->toBe('Hello');
});

it('stores network stats and main blocks', function () {
    $stat = $this->createStat(['connections' => [['nodeAddress' => '10.0.0.1:8001', 'inBound' => 1, 'outBound' => 2]]]);
    $mainBlock = $this->createMainBlock(['created_at' => '2040-01-01 00:00:00.123']);

    expect(Stat::query()->findOrFail($stat->id)->connections)->toBe([['nodeAddress' => '10.0.0.1:8001', 'inBound' => 1, 'outBound' => 2]])
        ->and(MainBlock::query()->findOrFail($mainBlock->address)->created_at->format('Y-m-d H:i:s.v'))->toBe('2040-01-01 00:00:00.123');
});

<?php

declare(strict_types=1);

use App\Xdag\Block\Block;
use App\Xdag\Block\Notification;
use App\Xdag\Exceptions\XdagException;

function walletEntry(Block $block, string $direction, string $amount, string $createdAt): void
{
    test()->createTransaction([
        'block_id' => $block->id,
        'view' => 'wallet',
        'direction' => $direction,
        'amount' => $amount,
        'created_at' => $createdAt,
    ]);
}

it('tells the kind of the block', function (string $type, string $check) {
    $block = $this->createBlock(['type' => $type]);

    expect(collect(['isMainBlock', 'isWalletBlock', 'isTransactionBlock', 'isSnapshotBlock'])->filter(fn (string $method) => $block->$method())->values()->all())
        ->toBe([$check]);
})->with([
    ['Main', 'isMainBlock'],
    ['Wallet', 'isWalletBlock'],
    ['Transaction', 'isTransactionBlock'],
    ['Snapshot', 'isSnapshotBlock'],
]);

it('tells whether the block exists on the blockchain', function () {
    expect($this->createBlock()->existsOnBlockchain())->toBeTrue()
        ->and(Block::factory()->notFound()->make()->existsOnBlockchain())->toBeFalse();
});

it('refuses to describe a block it has not fetched yet', function (string $method) {
    (new Block(['state' => null]))->$method();
})->with(['existsOnBlockchain', 'isMainBlock', 'isWalletBlock', 'isTransactionBlock', 'isSnapshotBlock'])
    ->throws(XdagException::class, 'Block cache is not filled.');

it('draws the balance of the last week from the whole history', function () {
    $this->travelTo('2031-05-10 12:00:00');

    $block = $this->createBlock();
    walletEntry($block, 'input', '100.000000000', '2031-04-01 08:00:00');
    walletEntry($block, 'input', '1.000000000', '2031-05-03 23:59:59');
    walletEntry($block, 'input', '10.000000000', '2031-05-04 00:00:00');
    walletEntry($block, 'earning', '64.000000000', '2031-05-07 10:00:00');
    walletEntry($block, 'output', '-30.000000001', '2031-05-10 11:59:59');
    $this->createTransaction(['block_id' => $block->id, 'view' => 'transaction', 'direction' => 'input', 'amount' => '999.000000000', 'created_at' => null]);

    expect($block->walletGraph('balance'))->toBe([
        'labels' => ['2031-05-04', '2031-05-05', '2031-05-06', '2031-05-07', '2031-05-08', '2031-05-09', '2031-05-10'],
        'values' => ['111.000000000', '111.000000000', '111.000000000', '175.000000000', '175.000000000', '175.000000000', '144.999999999'],
    ]);
});

it('never draws the balance below zero', function () {
    $this->travelTo('2031-05-10 12:00:00');

    $block = $this->createBlock();
    walletEntry($block, 'output', '-50.000000000', '2031-05-05 10:00:00');
    walletEntry($block, 'input', '20.000000000', '2031-05-08 10:00:00');

    expect($block->walletGraph('balance')['values'])
        ->toBe(['0.000000000', '0.000000000', '0.000000000', '0.000000000', '20.000000000', '20.000000000', '20.000000000']);
});

it('draws daily earnings and spendings of the last week', function () {
    $this->travelTo('2031-05-10 12:00:00');

    $block = $this->createBlock();
    walletEntry($block, 'input', '100.000000000', '2031-04-01 08:00:00');
    walletEntry($block, 'input', '10.000000000', '2031-05-04 00:00:00');
    walletEntry($block, 'earning', '64.000000000', '2031-05-07 10:00:00');
    walletEntry($block, 'snapshot', '1.500000000', '2031-05-07 11:00:00');
    walletEntry($block, 'output', '-30.000000001', '2031-05-10 11:59:59');
    walletEntry($block, 'output', '-0.000000001', '2031-05-10 00:00:00');

    expect($block->walletGraph('earnings'))->toBe([
        'labels' => ['2031-05-04', '2031-05-05', '2031-05-06', '2031-05-07', '2031-05-08', '2031-05-09', '2031-05-10'],
        'values' => ['10.000000000', '0.000000000', '0.000000000', '65.500000000', '0.000000000', '0.000000000', '0.000000000'],
    ])->and($block->walletGraph('spendings')['values'])->toBe([
        '0.000000000', '0.000000000', '0.000000000', '0.000000000', '0.000000000', '0.000000000', '30.000000002',
    ]);
});

it('draws an empty week for a block without history', function (string $type) {
    $this->travelTo('2031-05-10 00:00:00');

    expect($this->createBlock()->walletGraph($type))->toBe([
        'labels' => ['2031-05-04', '2031-05-05', '2031-05-06', '2031-05-07', '2031-05-08', '2031-05-09', '2031-05-10'],
        'values' => array_fill(0, 7, '0.000000000'),
    ]);
})->with(['balance', 'earnings', 'spendings']);

it('lists the notifications shown for the address and the hash of a block', function () {
    $this->travelTo('2031-05-10 12:00:00');

    $block = $this->createBlock();
    $this->createNotification(['id' => $block->address, 'message' => 'always']);
    $this->createNotification(['id' => $block->hash, 'message' => 'current', 'show_from' => '2031-05-10 11:00:00', 'show_to' => '2031-05-10 13:00:00']);

    $expired = $this->createNotification(['message' => 'expired', 'show_to' => '2031-05-10 11:59:59']);
    $upcoming = $this->createNotification(['message' => 'upcoming', 'show_from' => '2031-05-10 12:00:01']);

    expect($block->addressNotifications()->shown()->pluck('message')->all())->toBe(['always'])
        ->and($block->hashNotifications()->shown()->pluck('message')->all())->toBe(['current'])
        ->and(Notification::query()->whereKey([$expired->id, $upcoming->id])->shown()->exists())->toBeFalse();
});

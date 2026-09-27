<?php

declare(strict_types=1);

use App\Xdag\Block\Block;
use App\Xdag\Block\Listing\TransactionsListing;
use App\Xdag\Block\Listing\WalletListing;
use Illuminate\Http\Request;

/**
 * @param  array<string, mixed>  $query
 */
function walletListing(Block $block, array $query = []): WalletListing
{
    return new WalletListing($block, Request::create('/block/'.$block->id, 'GET', $query));
}

/**
 * @param  array<string, mixed>  $query
 */
function transactionsListing(Block $block, array $query = []): TransactionsListing
{
    return new TransactionsListing($block, Request::create('/block/'.$block->id, 'GET', $query));
}

/**
 * @return array<int, string>
 */
function listedRemarks(WalletListing $listing): array
{
    return collect($listing->get()->items())->pluck('remark')->all();
}

/**
 * @param  array<string, mixed>  $attributes
 */
function walletHistory(Block $block, array $attributes): void
{
    test()->createTransaction(['block_id' => $block->id, 'view' => 'wallet'] + $attributes);
}

beforeEach(function () {
    $this->block = $this->createBlock();
    $this->counterparty = blockAddress();

    walletHistory($this->block, ['direction' => 'input', 'amount' => '10.000000000', 'remark' => 'oldest', 'created_at' => '2031-05-01 00:00:00.000', 'address' => $this->counterparty]);
    walletHistory($this->block, ['direction' => 'output', 'amount' => '-2.500000000', 'remark' => 'paid 100%_off', 'created_at' => '2031-05-02 23:59:59.999']);
    walletHistory($this->block, ['direction' => 'earning', 'amount' => '64.000000000', 'remark' => 'reward', 'created_at' => '2031-05-03 12:00:00.000']);
    walletHistory($this->block, ['direction' => 'snapshot', 'amount' => '1.000000000', 'remark' => 'newest', 'created_at' => '2031-05-04 12:00:00.000', 'address' => $this->counterparty]);
});

it('lists the wallet history from the newest entry', function () {
    $listing = walletListing($this->block);

    expect(listedRemarks($listing))->toBe(['newest', 'reward', 'paid 100%_off', 'oldest'])
        ->and($listing->isFiltered())->toBeFalse()
        ->and($listing->errors())->toBe([])
        ->and($listing->earningsSum())->toBe('75.000000000')
        ->and($listing->spendingsSum())->toBe('2.500000000');
});

it('filters the wallet history', function (array $query, array $remarks, string $earnings, string $spendings) {
    $query = array_map(fn ($value) => $value === 'COUNTERPARTY' ? $this->counterparty : $value, $query);
    $listing = walletListing($this->block, $query);

    expect($listing->errors())->toBe([])
        ->and($listing->isFiltered())->toBeTrue()
        ->and(array_keys($listing->usedFilters()))->toBe(array_keys($query))
        ->and(listedRemarks($listing))->toBe($remarks)
        ->and($listing->earningsSum())->toBe($earnings)
        ->and($listing->spendingsSum())->toBe($spendings);
})->with([
    'address' => [['addresses_address' => 'COUNTERPARTY'], ['newest', 'oldest'], '11.000000000', '0'],
    'date from' => [['addresses_date_from' => '2031-05-02'], ['newest', 'reward', 'paid 100%_off'], '65.000000000', '2.500000000'],
    'date to, including its last millisecond' => [['addresses_date_to' => '2031-05-02'], ['paid 100%_off', 'oldest'], '10.000000000', '2.500000000'],
    'date range' => [['addresses_date_from' => '2031-05-02', 'addresses_date_to' => '2031-05-03'], ['reward', 'paid 100%_off'], '64.000000000', '2.500000000'],
    'absolute amount from' => [['addresses_amount_from' => '2.5'], ['reward', 'paid 100%_off', 'oldest'], '74.000000000', '2.500000000'],
    'absolute amount to' => [['addresses_amount_to' => '2.5'], ['newest', 'paid 100%_off'], '1.000000000', '2.500000000'],
    'directions' => [['addresses_directions' => ['output', 'snapshot']], ['newest', 'paid 100%_off'], '1.000000000', '2.500000000'],
    'remark' => [['addresses_remark' => 'EWES'], ['newest'], '1.000000000', '0'],
    'remark with like wildcards taken literally' => [['addresses_remark' => '100%_'], ['paid 100%_off'], '0', '2.500000000'],
]);

it('reports invalid wallet filters instead of applying them', function (array $query, string $filter) {
    $listing = walletListing($this->block, $query);

    expect($listing->errors())->toHaveKey($filter)
        ->and($listing->usedFilters())->not->toHaveKey($filter)
        ->and(listedRemarks($listing))->toHaveCount(4);
})->with([
    'address in another format' => [['addresses_address' => 'not-an-address'], 'addresses_address'],
    'date format' => [['addresses_date_from' => '01.05.2031'], 'addresses_date_from'],
    'dates in the wrong order' => [['addresses_date_from' => '2031-05-03', 'addresses_date_to' => '2031-05-02'], 'addresses_date_from'],
    'negative amount' => [['addresses_amount_from' => '-1'], 'addresses_amount_from'],
    'amounts in the wrong order' => [['addresses_amount_from' => '5', 'addresses_amount_to' => '1'], 'addresses_amount_to'],
    'unknown direction' => [['addresses_directions' => ['fee']], 'addresses_directions'],
    'direction not as a list' => [['addresses_directions' => 'input'], 'addresses_directions'],
    'remark as a list' => [['addresses_remark' => ['x']], 'addresses_remark'],
]);

it('pages through the wallet history', function () {
    $listing = walletListing($this->block, ['addresses_page' => '2', 'addresses_per_page' => '3']);
    $page = $listing->get();

    expect(listedRemarks($listing))->toBe(['oldest'])
        ->and($page->total())->toBe(4)
        ->and($page->lastPage())->toBe(2)
        ->and($page->url(1))->toContain('addresses_page=1');
});

it('falls back to the first page of the default size', function (array $query) {
    $listing = walletListing($this->block, $query);

    expect($listing->page())->toBe(1)
        ->and($listing->perPage())->toBeGreaterThanOrEqual(1);
})->with([
    'nothing' => [[]],
    'garbage' => [['addresses_page' => 'abc', 'addresses_per_page' => 'xyz']],
    'zero and negative' => [['addresses_page' => '0', 'addresses_per_page' => '-5']],
]);

it('lists the inputs, outputs and fee of a transaction block', function () {
    $block = $this->createBlock(['type' => 'Transaction']);
    $sender = walletAddress();
    $recipient = walletAddress();

    foreach ([['input', '100.000000000', $sender], ['output', '-99.900000000', $recipient], ['fee', '-0.100000000', $sender]] as [$direction, $amount, $address]) {
        $this->createTransaction(['block_id' => $block->id, 'view' => 'transaction', 'direction' => $direction, 'amount' => $amount, 'address' => $address, 'created_at' => null, 'remark' => null]);
    }

    $all = transactionsListing($block);
    $bySender = transactionsListing($block, ['transactions_address' => $sender]);
    $large = transactionsListing($block, ['transactions_amount_from' => '1', 'transactions_amount_to' => '100']);
    $fees = transactionsListing($block, ['transactions_directions' => ['fee']]);

    expect(collect($all->get()->items())->pluck('direction')->all())->toBe(['input', 'output', 'fee'])
        ->and([$all->inputsSum(), $all->outputsSum(), $all->feesSum()])->toBe(['100.000000000', '99.900000000', '0.100000000'])
        ->and(collect($bySender->get()->items())->pluck('direction')->all())->toBe(['input', 'fee'])
        ->and([$bySender->inputsSum(), $bySender->outputsSum(), $bySender->feesSum()])->toBe(['100.000000000', '0', '0.100000000'])
        ->and(collect($large->get()->items())->pluck('direction')->all())->toBe(['input', 'output'])
        ->and(collect($fees->get()->items())->pluck('direction')->all())->toBe(['fee'])
        ->and(transactionsListing($block, ['transactions_directions' => ['earning']])->errors())->toHaveKey('transactions_directions')
        ->and(transactionsListing($block, ['transactions_address' => blockAddress()])->errors())->toHaveKey('transactions_address');
});

it('does not mix up the two views of a block', function () {
    expect(transactionsListing($this->block)->get()->total())->toBe(0)
        ->and(transactionsListing($this->block)->inputsSum())->toBe('0');
});

<?php

declare(strict_types=1);

use App\Xdag\Block\Block;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    $this->withoutNodeSynchronization();
    $this->travelTo('2031-05-10 12:30:00');
});

/**
 * @return array<string, mixed>
 */
function streamedJson(TestResponse $response): array
{
    $response->assertOk()->assertHeader('Content-Type', 'application/json');

    return json_decode($response->streamedContent(), true, 512, JSON_THROW_ON_ERROR);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function walletRecord(Block $block, array $attributes): void
{
    test()->createTransaction(['block_id' => $block->id, 'view' => 'wallet'] + $attributes);
}

function walletBlockWithHistory(): Block
{
    $block = test()->createBlock([
        'balance' => '90.623456789',
        'remark' => 'pool.example.com',
        'difficulty' => '1a2b3c',
        'created_at' => '2031-05-01 08:09:10.123',
        'timestamp' => '1d2e3f',
        'flags' => '1c',
    ]);

    walletRecord($block, ['direction' => 'input', 'amount' => '100.123456789', 'remark' => 'thanks', 'created_at' => '2031-05-09 10:00:00.000', 'address' => 'InputFrom'.substr(blockAddress(), 9)]);
    walletRecord($block, ['direction' => 'output', 'amount' => '-10.500000000', 'remark' => null, 'created_at' => '2031-05-10 11:00:00.250', 'address' => 'OutputTo'.substr(blockAddress(), 8)]);
    walletRecord($block, ['direction' => 'snapshot', 'amount' => '1.000000000', 'remark' => null, 'created_at' => '2031-04-01 00:00:00.000', 'address' => $block->address]);

    return $block;
}

it('describes a wallet block with its whole history', function () {
    $block = walletBlockWithHistory();
    $this->createNotification(['id' => $block->address, 'type' => 'warning', 'message' => 'Exchange wallet']);
    $entries = $block->transactions()->orderByDesc('created_at')->get();
    $url = 'http://localhost:8000/api/block/'.$block->id;

    expect(streamedJson($this->get('/api/block/'.$block->id)))->toBe([
        'time' => '2031-05-01 08:09:10.123',
        'timestamp' => '1d2e3f',
        'flags' => '1c',
        'state' => 'Accepted',
        'file_pos' => '',
        'file' => '',
        'hash' => $block->hash,
        'remark' => 'pool.example.com',
        'difficulty' => '1a2b3c',
        'balance_address' => $block->address,
        'balance' => '90.623456789',
        'ui_notifications' => [['type' => 'warning', 'message' => 'Exchange wallet']],
        'block_as_transaction' => [],
        'block_as_address' => [
            ['direction' => 'output', 'address' => $entries[0]->address, 'amount' => '10.500000000', 'time' => '2031-05-10 11:00:00.250', 'remark' => null],
            ['direction' => 'input', 'address' => $entries[1]->address, 'amount' => '100.123456789', 'time' => '2031-05-09 10:00:00.000', 'remark' => 'thanks'],
            ['direction' => 'snapshot', 'address' => $block->address, 'amount' => '1.000000000', 'time' => '2031-04-01 00:00:00.000', 'remark' => null],
        ],
        'balances_last_week' => [
            '2031-05-04' => '1.000000000',
            '2031-05-05' => '1.000000000',
            '2031-05-06' => '1.000000000',
            '2031-05-07' => '1.000000000',
            '2031-05-08' => '1.000000000',
            '2031-05-09' => '101.123456789',
            '2031-05-10' => '90.623456789',
        ],
        'earnings_last_week' => [
            '2031-05-04' => '0.000000000',
            '2031-05-05' => '0.000000000',
            '2031-05-06' => '0.000000000',
            '2031-05-07' => '0.000000000',
            '2031-05-08' => '0.000000000',
            '2031-05-09' => '100.123456789',
            '2031-05-10' => '0.000000000',
        ],
        'spendings_last_week' => [
            '2031-05-04' => '0.000000000',
            '2031-05-05' => '0.000000000',
            '2031-05-06' => '0.000000000',
            '2031-05-07' => '0.000000000',
            '2031-05-08' => '0.000000000',
            '2031-05-09' => '0.000000000',
            '2031-05-10' => '10.500000000',
        ],
        'balance_change_last_24_hours' => '-10.500000000',
        'earnings_change_last_24_hours' => '-100.123456789',
        'spendings_change_last_24_hours' => '10.500000000',
        'total_earnings' => '101.123456789',
        'total_spendings' => '10.500000000',
        'page_earnings_sum' => '101.123456789',
        'page_spendings_sum' => '10.500000000',
        'filtered_earnings_sum' => '101.123456789',
        'filtered_spendings_sum' => '10.500000000',
        'kind' => 'Wallet block',
        'transactions_pagination' => [
            'current_page' => 1,
            'last_page' => 1,
            'total' => 0,
            'per_page' => 10000000000000,
            'links' => [
                'prev' => null,
                'next' => null,
                'first' => "$url?transactions_page=1&transactions_per_page=10000000000000",
                'last' => "$url?transactions_page=1&transactions_per_page=10000000000000",
            ],
        ],
        'addresses_pagination' => [
            'current_page' => 1,
            'last_page' => 1,
            'total' => 3,
            'per_page' => 10000000000000,
            'links' => [
                'prev' => null,
                'next' => null,
                'first' => "$url?addresses_page=1&addresses_per_page=10000000000000",
                'last' => "$url?addresses_page=1&addresses_per_page=10000000000000",
            ],
        ],
    ]);
});

it('gives the height of main blocks only', function () {
    $main = $this->trackBlock(Block::factory()->main()->create(['height' => 3_500_000]));

    expect(streamedJson($this->get('/api/block/'.$main->id)))
        ->toHaveKey('height', 3_500_000)
        ->toHaveKey('kind', 'Main block')
        ->and(streamedJson($this->get('/api/block/'.$this->createBlock()->id)))->not->toHaveKey('height');
});

it('describes a transaction block with its inputs, outputs and fee', function () {
    $block = $this->createBlock(['type' => 'Transaction', 'balance' => '0.000000000']);
    $sender = walletAddress();
    $recipient = walletAddress();

    foreach ([['input', '100.000000000', $sender], ['output', '-99.900000000', $recipient], ['fee', '-0.100000000', $sender]] as [$direction, $amount, $address]) {
        $this->createTransaction(['block_id' => $block->id, 'view' => 'transaction', 'direction' => $direction, 'amount' => $amount, 'address' => $address, 'created_at' => null, 'remark' => null]);
    }

    $json = streamedJson($this->get('/api/block/'.$block->id));

    expect($json['block_as_transaction'])->toBe([
        ['direction' => 'input', 'address' => $sender, 'amount' => '100.000000000'],
        ['direction' => 'output', 'address' => $recipient, 'amount' => '99.900000000'],
        ['direction' => 'fee', 'address' => $sender, 'amount' => '0.100000000'],
    ])->and($json['block_as_address'])->toBe([])
        ->and(array_slice($json, array_search('block_as_address', array_keys($json)) + 1))->toMatchArray([
            'total_fee' => '0.100000000',
            'total_inputs' => '100.000000000',
            'total_outputs' => '99.900000000',
            'page_fee_sum' => '0.100000000',
            'page_inputs_sum' => '100.000000000',
            'page_outputs_sum' => '99.900000000',
            'filtered_fee_sum' => '0.100000000',
            'filtered_inputs_sum' => '100.000000000',
            'filtered_outputs_sum' => '99.900000000',
            'kind' => 'Transaction block',
        ])
        ->and($json)->not->toHaveKey('balances_last_week');
});

it('pages through a filtered wallet history and ignores invalid filters', function () {
    $block = walletBlockWithHistory();
    $url = 'http://localhost:8000/api/block/'.$block->id;
    $filters = 'addresses_directions%5B0%5D=input&addresses_directions%5B1%5D=output';

    $json = streamedJson($this->get("/api/block/{$block->id}?addresses_directions[]=input&addresses_directions[]=output&addresses_amount_from=abc&addresses_per_page=1&addresses_page=2"));

    expect($json['block_as_address'])->toHaveCount(1)
        ->and($json['block_as_address'][0])->toMatchArray(['direction' => 'input', 'amount' => '100.123456789'])
        ->and($json['page_earnings_sum'])->toBe('100.123456789')
        ->and($json['page_spendings_sum'])->toBe('0.000000000')
        ->and($json['filtered_earnings_sum'])->toBe('100.123456789')
        ->and($json['filtered_spendings_sum'])->toBe('10.500000000')
        ->and($json['total_earnings'])->toBe('101.123456789')
        ->and($json['addresses_pagination'])->toBe([
            'current_page' => 2,
            'last_page' => 2,
            'total' => 2,
            'per_page' => 1,
            'links' => [
                'prev' => "$url?$filters&addresses_page=1&addresses_per_page=1",
                'next' => null,
                'first' => "$url?$filters&addresses_page=1&addresses_per_page=1",
                'last' => "$url?$filters&addresses_page=2&addresses_per_page=1",
            ],
        ]);
});

it('links to the next page while there are more entries', function () {
    $block = walletBlockWithHistory();

    $json = streamedJson($this->get("/api/block/{$block->id}?addresses_per_page=2"));

    expect($json['block_as_address'])->toHaveCount(2)
        ->and($json['addresses_pagination']['last_page'])->toBe(2)
        ->and($json['addresses_pagination']['links']['next'])->toBe("http://localhost:8000/api/block/{$block->id}?addresses_page=2&addresses_per_page=2");
});

it('describes blocks whose address contains slashes and plus signs', function () {
    $address = substr_replace(blockAddress(), '/+//+', 5, 5);
    $this->createBlock(['id' => $address, 'address' => $address]);

    expect(streamedJson($this->get('/api/block/'.$address)))
        ->toHaveKey('balance_address', $address)
        ->and(streamedJson($this->get('/api/block/'.$address))['addresses_pagination']['links']['first'])->toStartWith("http://localhost:8000/api/block/$address?");
});

it('rejects malformed ids and unknown blocks', function (Closure $id) {
    Http::fake();

    $this->getJson('/api/block/'.$id())
        ->assertStatus(422)
        ->assertExactJson(['error' => 'invalid_input', 'message' => 'Incorrect address, block hash or main block height.']);
})->with([
    'malformed' => fn () => 'not-a-block',
    'unknown' => fn () => test()->trackBlock(Block::factory()->notFound()->create())->id,
]);

it('reports a node that cannot be reached', function () {
    config(['xdag.rpc_url' => 'http://node.test:10001', 'explorer.getblock_binary_name' => '']);

    $id = blockHash();
    $this->trackBlock($id);

    Http::fake(fn () => Http::failedConnection());

    $this->getJson('/api/block/'.$id)
        ->assertStatus(500)
        ->assertExactJson(['error' => 'internal_error', 'message' => "RPC method 'xdag_getBlockByHash' could not reach the node."]);
});

it('gives the balance of an address', function () {
    config(['xdag.rpc_url' => 'http://node.test:10001']);

    $address = walletAddress();
    $this->trackBalance($address);

    fakeNode(['xdag_getBalance' => '1234.000000001']);

    $this->getJson('/api/balance/'.$address)->assertOk()->assertExactJson(['balance' => '1234.000000001']);
});

it('gives the balance of a block it already knows', function () {
    Http::fake();

    $block = $this->createBlock(['balance' => '5.500000000']);

    $this->getJson('/api/balance/'.$block->id)->assertOk()->assertExactJson(['balance' => '5.500000000']);

    Http::assertNothingSent();
});

it('rejects balances of malformed ids and unknown addresses', function () {
    config(['xdag.rpc_url' => 'http://node.test:10001']);

    $unknown = walletAddress();
    $this->trackBalance($unknown);

    fakeNode(['xdag_getBalance' => rpcWithoutResult()]);

    foreach (['not-an-address', $unknown] as $id) {
        $this->getJson('/api/balance/'.$id)
            ->assertStatus(422)
            ->assertExactJson(['error' => 'invalid_input', 'message' => 'Incorrect address, block hash or main block height.']);
    }
});

it('reports a node that cannot be asked for a balance', function () {
    config(['xdag.rpc_url' => 'http://node.test:10001']);

    $address = walletAddress();
    $this->trackBalance($address);

    Http::fake(fn () => Http::failedConnection());

    $this->getJson('/api/balance/'.$address)
        ->assertStatus(500)
        ->assertJsonPath('error', 'internal_error');
});

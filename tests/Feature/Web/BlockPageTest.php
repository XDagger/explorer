<?php

declare(strict_types=1);

use App\Xdag\Block\Block;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->withoutNodeSynchronization();
    $this->travelTo('2031-05-10 12:30:00');
});

/**
 * @param  array<string, mixed>  $attributes
 */
function walletEntryOf(Block $block, array $attributes): void
{
    test()->createTransaction(['block_id' => $block->id, 'view' => 'wallet'] + $attributes);
}

it('shows the details and the wallet history of a block', function () {
    $block = $this->createBlock([
        'balance' => '1500.123456789',
        'remark' => 'visit https://pool.example.com',
        'difficulty' => '1a2b3c',
        'created_at' => '2031-05-01 08:09:10.123',
    ]);

    walletEntryOf($block, ['direction' => 'input', 'amount' => '100.123456789', 'remark' => 'thanks https://example.org/x', 'created_at' => '2031-05-09 10:00:00.000']);
    walletEntryOf($block, ['direction' => 'output', 'amount' => '-10.500000000', 'created_at' => '2031-05-10 11:00:00.000']);

    $this->get('/block/'.$block->id)
        ->assertOk()
        ->assertSee('2031-05-01 08:09:10.123')
        ->assertSee('<a href="'.route('block', ['id' => $block->hash]).'"', false)
        ->assertSee('visit <a href="https://pool.example.com" target="_blank">https://pool.example.com</a>', false)
        ->assertSee('1a2b3c')
        ->assertSee('1,500.123456789')
        ->assertSee('thanks <a href="https://example.org/x" target="_blank">https://example.org/x</a>', false)
        ->assertSee('100.123456789')
        ->assertSee('10.500000000')
        ->assertViewHas('balanceGraph', fn (array $graph) => $graph['labels'][6] === '2031-05-10' && $graph['values'][6] === '89.623456789')
        ->assertViewHas('earningsChange', fn (array $change) => $change['previousValue'] === '100.123456789' && $change['currentValue'] === '0.000000000');
});

it('shows blocks whose address contains slashes and plus signs', function () {
    $address = substr_replace(blockAddress(), '/+//+', 5, 5);
    $block = $this->createBlock(['id' => $address, 'address' => $address, 'balance' => '42.000000000']);

    $this->get('/block/'.$address)
        ->assertOk()
        ->assertViewHas('block', fn (Block $shown) => $shown->is($block))
        ->assertSee('42.000000000');
});

it('shows the inputs, outputs and fee of a transaction block before its wallet history', function () {
    $block = $this->createBlock(['type' => 'Transaction']);
    $sender = walletAddress();

    foreach ([['input', '100.000000000'], ['output', '-99.900000000'], ['fee', '-0.100000000']] as [$direction, $amount]) {
        $this->createTransaction(['block_id' => $block->id, 'view' => 'transaction', 'direction' => $direction, 'amount' => $amount, 'address' => $sender, 'created_at' => null, 'remark' => null]);
    }

    $this->get('/block/'.$block->id)
        ->assertOk()
        ->assertSeeInOrder(['Block as transaction', 'Block as address'])
        ->assertSee('1 input')
        ->assertSee('99.900000000')
        ->assertSee('0.100000000')
        ->assertSee($sender);
});

it('shows the wallet history of other blocks before their transactions', function () {
    $block = $this->createBlock();

    $this->get('/block/'.$block->id)->assertOk()->assertSeeInOrder(['Block as address', 'Block as transaction']);
});

it('links main blocks to their neighbours', function () {
    $block = Block::factory()->main()->create(['height' => 3_500_000]);
    $this->trackBlock($block);

    $this->get('/block/'.$block->id)
        ->assertOk()
        ->assertSee('<a href="/block/3500000" rel="nofollow" class="leading-normal opacity-75 break-words text-grey-darkest">Main block 3500000</a>', false)
        ->assertSee('href="/block/3499999"', false)
        ->assertSee('href="/block/3500001"', false);
});

it('applies the filters of the wallet history and keeps them while paging', function () {
    $block = $this->createBlock();

    foreach (range(1, 5) as $day) {
        walletEntryOf($block, ['direction' => 'input', 'amount' => "$day.000000000", 'remark' => "entry $day", 'created_at' => "2031-05-0$day 10:00:00.000"]);
    }

    $this->get('/block/'.$block->id.'?addresses_amount_from=2&addresses_per_page=2')
        ->assertOk()
        ->assertSee('Applied filters')
        ->assertSee('Amount from')
        ->assertSee('2.000000000')
        ->assertSee('Filtered entries: <strong>4</strong>', false)
        ->assertSee('entry 5')
        ->assertSee('entry 4')
        ->assertDontSee('entry 3')
        ->assertSee('addresses_amount_from=2&amp;addresses_per_page=2&amp;addresses_page=2#block-as-address', false)
        ->assertSee('+14.000000000');
});

it('explains filters it cannot apply', function () {
    $block = $this->createBlock();

    $this->get('/block/'.$block->id.'?addresses_amount_from=abc&addresses_remark[]=x&transactions_directions[]=earning')
        ->assertOk()
        ->assertDontSee('Applied filters')
        ->assertSee('The addresses amount from field must be a number.')
        ->assertSee('The addresses remark field must be a string.')
        ->assertSee('The selected transactions_directions.0 is invalid.');
});

it('shows the notifications about the block', function () {
    $block = $this->createBlock();
    $this->createNotification(['id' => $block->address, 'type' => 'warning', 'message' => 'This address belongs to an <b>exchange</b>']);
    $this->createNotification(['id' => $block->hash, 'message' => 'Known block', 'show_to' => now()->subMinute()]);

    $this->get('/block/'.$block->id)
        ->assertOk()
        ->assertSee('This address belongs to an <b>exchange</b>', false)
        ->assertDontSee('Known block');
});

it('sends searches for malformed ids back home', function (string $id) {
    Http::fake();

    $this->get('/block/'.$id)->assertRedirect('/?400');

    Http::assertNothingSent();
})->with(['not-a-block', str_repeat('Z', 64), '12345678901']);

it('sends searches for blocks the node does not know back home', function () {
    $block = $this->trackBlock(Block::factory()->notFound()->create());

    $this->get('/block/'.$block->id)->assertRedirect('/?404');
});

it('looks up blocks it has not seen yet at the node', function () {
    config(['xdag.rpc_url' => 'http://node.test:10001', 'explorer.getblock_binary_name' => '']);

    $node = nodeBlock(['balance' => '7.000000001']);
    $this->trackBlock($node['address']);

    fakeNode(['xdag_getBlockByHash' => $node]);

    $this->get('/block/'.$node['address'])->assertOk()->assertSee('7.000000001');
});

it('shows the error page when the node cannot be reached', function () {
    config(['app.debug' => false, 'xdag.rpc_url' => 'http://node.test:10001', 'explorer.getblock_binary_name' => '']);

    $id = blockHash();
    $this->trackBlock($id);

    Http::fake(fn () => Http::failedConnection());

    $this->get('/block/'.$id)->assertServerError()->assertSee('Something went wrong');

    expect(Block::query()->find($id))->toBeNull();
});

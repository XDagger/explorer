<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Home page
|--------------------------------------------------------------------------
|
| The home page shows the network wide stats and main blocks. Creating those
| takes the network lock, so the rows found here are the ones created here.
|
*/

beforeEach(function () {
    $this->travelTo('2031-05-10 12:30:00');
});

it('shows the latest network stats', function () {
    $this->createStat(['created_at' => now()->subMinute()]);
    $this->createStat([
        'blocks' => 4_100_000_123,
        'main_blocks' => 3_500_002,
        'supply' => '1234567890.123456789',
        'network_hashrate' => 12345678901,
        'difficulty' => '1a2b3c4d5e6f',
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('4,100,000,123')
        ->assertSee('<a href="/block/3500002" title="Show latest main block" v-tippy>3,500,002</a>', false)
        ->assertSee('1,234,567,890 XDAG')
        ->assertSee('11.5 Gh/s')
        ->assertSee('1a2b3c4d5e6f');
});

it('counts the blocks created in the last minute', function (int $previous, int $latest, int $new) {
    $this->createStat(['blocks' => $previous, 'created_at' => now()->subMinute()]);
    $this->createStat(['blocks' => $latest]);

    $this->get('/')->assertOk()->assertViewHas('numberOfNewBlocksLastMinute', $new);
})->with([
    'more blocks' => [1000, 1100, 100],
    'fewer blocks after the node restarted' => [1100, 1050, 0],
]);

it('counts no new blocks from the very first stats', function () {
    $this->createStat();

    $this->get('/')->assertOk()->assertViewHas('numberOfNewBlocksLastMinute', 0);
});

it('charts the hourly network hashrate and the blocks created each minute', function () {
    $this->createStat(['network_hashrate' => 10 * 1024 * 1024, 'blocks' => 1000, 'created_at' => '2031-05-10 10:15:00']);
    $this->createStat(['network_hashrate' => 20 * 1024 * 1024, 'blocks' => 1100, 'created_at' => '2031-05-10 11:10:00']);
    $this->createStat(['network_hashrate' => 40 * 1024 * 1024, 'blocks' => 1050, 'created_at' => '2031-05-10 11:50:00']);

    $this->get('/')
        ->assertOk()
        ->assertViewHas('hashrateChartData', ['labels' => ['05-10 10:00', '05-10 11:00'], 'values' => [10.0, 30.0]])
        ->assertViewHas('newBlocksChartData', ['labels' => ['11:10', '11:50'], 'values' => [100, 0]]);
});

it('charts the blocks of the last hour only', function () {
    foreach (range(70, 0) as $minutesAgo) {
        $this->createStat(['blocks' => 1000 + (70 - $minutesAgo) * 3, 'created_at' => now()->subMinutes($minutesAgo)]);
    }

    $this->get('/')
        ->assertOk()
        ->assertViewHas('newBlocksChartData', fn (array $data) => count($data['labels']) === 60
            && $data['labels'][0] === '11:31'
            && $data['labels'][59] === '12:30'
            && $data['values'] === array_fill(0, 60, 3));
});

it('compares the network hashrate of the last hour with the hour before', function () {
    foreach (range(119, 60) as $minutesAgo) {
        $this->createStat(['network_hashrate' => 1000, 'created_at' => now()->subMinutes($minutesAgo)]);
    }

    foreach (range(59, 0) as $minutesAgo) {
        $this->createStat(['network_hashrate' => 1500, 'created_at' => now()->subMinutes($minutesAgo)]);
    }

    $this->get('/')
        ->assertOk()
        ->assertViewHas('hashrateChange', fn (array $change) => $change['increased'] === true && $change['percentageChange'] === '50.00')
        ->assertSee('Network hashrate increased by 50% in last hour');
});

it('lists the latest twenty main blocks with their pools', function () {
    $this->createStat();
    $tooOld = $this->createMainBlock(['height' => 1000]);
    $this->createMainBlock(['height' => 1001, 'remark' => 'https://pool.example.com/stats', 'created_at' => '2031-05-10 12:29:00.123']);
    $this->createMainBlock(['height' => 1002, 'remark' => 'plain pool name']);

    foreach (range(1003, 1020) as $height) {
        $this->createMainBlock(['height' => $height]);
    }

    $this->get('/')
        ->assertOk()
        ->assertViewHas('mainBlocks', fn ($mainBlocks) => $mainBlocks->pluck('height')->all() === range(1020, 1001))
        ->assertDontSee($tooOld->address)
        ->assertSee('<a href="https://pool.example.com/stats" target="_blank" class="font-normal" style="color: inherit">2031-05-10 12:29:00 UTC</a>', false)
        ->assertSee('pool.example.com')
        ->assertSee('plain pool name')
        ->assertSee('No pool tag');
});

it('explains why a searched block could not be shown', function (string $query, string $message) {
    $this->createStat();

    $this->get("/?$query")->assertOk()->assertSee($message);
})->with([
    ['400', 'Incorrect address, block hash or main block height.'],
    ['404', 'Block was not found. Please make sure you entered correct address, block hash or main block height.'],
]);

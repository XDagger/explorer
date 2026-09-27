<?php

declare(strict_types=1);

it('offers the balance checker', function () {
    $this->withoutNodeSynchronization()
        ->get('/balance')
        ->assertOk()
        ->assertSee('Balance on block');
});

it('feeds the mining calculator with the latest network hashrate and block reward', function () {
    $this->createStat(['created_at' => now()->subMinute()]);
    $this->createStat(['network_hashrate' => 25600000000, 'block_reward' => '64.500000000']);

    $this->get('/mining-calculator')
        ->assertOk()
        ->assertViewHas('network_hashrate', 25600000000)
        ->assertViewHas('block_reward', '64.500000000')
        ->assertSee(':network-hashrate="25600000000" :reward="64"', false);
});

it('documents the API with the latest main block as the example', function () {
    $this->createStat();
    $this->createMainBlock(['height' => 1]);
    $latest = $this->createMainBlock(['height' => 9_999_999]);

    $this->get('/api-docs')
        ->assertOk()
        ->assertSee('<a href="/api/block/'.$latest->address.'" target="_blank">Try it</a>', false)
        ->assertDontSee('~LATEST_MAIN_BLOCK_ADDRESS~');
});

it('documents the API with the first block as the example before any main block is known', function () {
    $this->createStat();

    $this->get('/api-docs')
        ->assertOk()
        ->assertSee('<a href="/api/block/1" target="_blank">Try it</a>', false);
});

it('shows the important message on every page', function () {
    config(['explorer.important_ui_message' => 'Network upgrade <b>tonight</b>']);

    $this->withoutNodeSynchronization()
        ->get('/balance')
        ->assertOk()
        ->assertSee('Network upgrade <b>tonight</b>', false);
});

it('shows a friendly page for unknown addresses', function () {
    $this->get('/no-such-page')
        ->assertNotFound()
        ->assertSee('Page not found')
        ->assertSee('<title>XDAG', false);
});

it('shows a friendly page when something breaks', function () {
    config(['app.debug' => false]);

    Route::get('/test-failure', fn () => throw new RuntimeException('boom'));

    $this->get('/test-failure')
        ->assertServerError()
        ->assertSee('Something went wrong')
        ->assertDontSee('boom');
});

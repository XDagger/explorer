<?php

declare(strict_types=1);

use Database\Factories\XdagIdentifier;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The suite runs in parallel against a shared database, so no test may rely on
| table state it did not create. Build what you need through the create*()
| methods of Tests\TestCase: they remove the rows again afterwards, and the
| ones for network stats and main blocks hold a lock that keeps tests of the
| network wide state from running at the same time.
|
*/

pest()->extend(TestCase::class)->in('Feature', 'Unit');

function blockHash(): string
{
    return XdagIdentifier::hash();
}

function blockAddress(): string
{
    return XdagIdentifier::address();
}

function walletAddress(): string
{
    return XdagIdentifier::walletAddress();
}

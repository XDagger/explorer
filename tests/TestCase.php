<?php

declare(strict_types=1);

namespace Tests;

use App\Xdag\Block\Balance;
use App\Xdag\Block\Block;
use App\Xdag\Block\MainBlock;
use App\Xdag\Block\Notification;
use App\Xdag\Block\Transaction;
use App\Xdag\Network\Stat;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    private const NETWORK_LOCK = 'tests:network';

    private const NETWORK_LOCK_TIMEOUT = 120;

    private bool $holdsNetworkLock = false;

    private int $statsWatermark = 0;

    /** @var array<int, string> */
    private array $preexistingMainBlocks = [];

    /** @var array<int, string> */
    private array $blockIds = [];

    /** @var array<int, string> */
    private array $balanceIds = [];

    /** @var array<int, string> */
    private array $notificationIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    /**
     * The stats and main blocks tables hold state of the whole network: the
     * application reads their latest rows, and fetching the stats rewrites
     * them. A test that touches either holds this lock until it has cleaned
     * up, so these tests run one at a time across all parallel processes and
     * each sees only the rows it created itself.
     */
    public function lockNetwork(): void
    {
        if ($this->holdsNetworkLock) {
            return;
        }

        if ((int) DB::scalar('select get_lock(?, ?)', [self::NETWORK_LOCK, self::NETWORK_LOCK_TIMEOUT]) !== 1) {
            throw new RuntimeException('Timed out waiting for the network state lock.');
        }

        $this->holdsNetworkLock = true;
        $this->statsWatermark = (int) Stat::query()->max('id');
        $this->preexistingMainBlocks = MainBlock::query()->pluck('address')->all();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createStat(array $attributes = []): Stat
    {
        $this->lockNetwork();

        return Stat::factory()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createMainBlock(array $attributes = []): MainBlock
    {
        $this->lockNetwork();

        return MainBlock::factory()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createBlock(array $attributes = []): Block
    {
        return $this->trackBlock(Block::factory()->create($attributes));
    }

    public function trackBlock(Block|string $block): Block|string
    {
        $this->blockIds[] = $block instanceof Block ? $block->getKey() : $block;

        return $block;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createTransaction(array $attributes = []): Transaction
    {
        $attributes['block_id'] ??= $this->createBlock()->getKey();

        return Transaction::factory()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createBalance(array $attributes = []): Balance
    {
        return $this->trackBalance(Balance::factory()->create($attributes));
    }

    public function trackBalance(Balance|string $balance): Balance|string
    {
        $this->balanceIds[] = $balance instanceof Balance ? $balance->getKey() : $balance;

        return $balance;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createNotification(array $attributes = []): Notification
    {
        $notification = Notification::factory()->create($attributes);

        $this->notificationIds[] = $notification->getKey();

        return $notification;
    }

    protected function tearDown(): void
    {
        if ($this->blockIds !== []) {
            Transaction::query()->whereIn('block_id', $this->blockIds)->delete();
            Block::query()->whereKey($this->blockIds)->delete();
        }

        if ($this->balanceIds !== []) {
            Balance::query()->whereKey($this->balanceIds)->delete();
        }

        if ($this->notificationIds !== []) {
            Notification::query()->whereKey($this->notificationIds)->delete();
        }

        if ($this->holdsNetworkLock) {
            Stat::query()->where('id', '>', $this->statsWatermark)->delete();
            MainBlock::query()->whereNotIn('address', $this->preexistingMainBlocks)->delete();

            DB::scalar('select release_lock(?)', [self::NETWORK_LOCK]);
        }

        $this->holdsNetworkLock = false;
        $this->blockIds = [];
        $this->balanceIds = [];
        $this->notificationIds = [];

        parent::tearDown();
    }
}

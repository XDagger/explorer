<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Database\Factories\XdagIdentifier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DevelopmentSeeder extends Seeder
{
    public const WALLETS = 6;

    public const TRANSFERS = 300;

    public const STATS_DAYS = 3;

    private const CACHE_DAYS = 30;

    private const MAIN_BLOCK_SECONDS = 64;

    private const CACHED_MAIN_BLOCKS = 200;

    private const POOLS = ['https://pool.xdag.example/stats', 'xdagpool.example', 'solo miner', null];

    private CarbonImmutable $now;

    private int $latestHeight;

    /** @var array<int, string> */
    private array $wallets = [];

    /** @var array<int, string> */
    private array $mainBlocks = [];

    /** @var array<int, string> */
    private array $transactionBlocks = [];

    /** @var array<int, string> */
    private array $blockIds = [];

    /** @var array<int, string> */
    private array $notificationIds = [];

    /** @var array<int, array<string, mixed>> */
    private array $blockRows = [];

    /** @var array<int, array<string, mixed>> */
    private array $transactionRows = [];

    public function run(): void
    {
        $this->now = CarbonImmutable::now('UTC')->startOfSecond();

        $this->seedStats();
        $this->seedMainBlocks();
        $this->seedWalletsAndTransfers();
        $this->seedNotifications();

        foreach (array_chunk($this->blockRows, 500) as $chunk) {
            DB::table('blocks')->insert($chunk);
        }

        foreach (array_chunk($this->transactionRows, 1000) as $chunk) {
            DB::table('block_transactions')->insert($chunk);
        }
    }

    /**
     * @return array<string, string>
     */
    public function summary(): array
    {
        return [
            'Latest main block' => '/block/'.$this->latestHeight,
            'Main block by address' => '/block/'.$this->mainBlocks[0],
            'Wallet' => '/block/'.$this->wallets[0],
            'Transaction block' => '/block/'.$this->transactionBlocks[0],
            'Wallet in the API' => '/api/block/'.$this->wallets[0],
        ];
    }

    /**
     * @return array<int, string>
     */
    public function blockIds(): array
    {
        return $this->blockIds;
    }

    /**
     * @return array<int, string>
     */
    public function notificationIds(): array
    {
        return $this->notificationIds;
    }

    private function seedStats(): void
    {
        $minutes = self::STATS_DAYS * 24 * 60;
        $blocks = 4_100_000_000;
        $firstHeight = 3_500_000;
        $difficulty = bin2hex(random_bytes(13));
        $rows = [];

        for ($minute = $minutes - 1; $minute >= 0; $minute--) {
            $createdAt = $this->now->subMinutes($minute);
            $elapsed = ($minutes - 1 - $minute) * 60;
            $mainBlocks = $firstHeight + intdiv($elapsed, self::MAIN_BLOCK_SECONDS);
            $blocks += random_int(20, 80);
            $hashrate = (int) (30 * 1024 ** 3 * (1 + 0.15 * sin($elapsed / 10800) + random_int(-30, 30) / 1000));
            $supply = bcadd('1234567890.000000000', bcmul((string) ($mainBlocks - $firstHeight), '64', 9), 9);

            $rows[] = [
                'synchronized' => true,
                'version' => '0.8.3',
                'network_type' => 'mainnet',
                'blocks' => $blocks,
                'network_blocks' => $blocks + random_int(0, 10),
                'main_blocks' => $mainBlocks,
                'network_main_blocks' => $mainBlocks,
                'difficulty' => $difficulty,
                'network_difficulty' => $difficulty,
                'supply' => $supply,
                'network_supply' => $supply,
                'block_reward' => '64.000000000',
                'hashrate' => (int) ($hashrate * 0.4),
                'network_hashrate' => $hashrate,
                'connections' => json_encode([
                    ['nodeAddress' => '10.0.0.11:8001', 'connectTime' => 0, 'inBound' => 0, 'outBound' => 0],
                    ['nodeAddress' => '10.0.0.12:8001', 'connectTime' => 0, 'inBound' => 0, 'outBound' => 0],
                    ['nodeAddress' => '10.0.0.13:8001', 'connectTime' => 0, 'inBound' => 0, 'outBound' => 0],
                ]),
                'created_at' => $this->format($createdAt),
            ];

            $this->latestHeight = $mainBlocks;
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('stats')->insert($chunk);
        }
    }

    /**
     * The main blocks table holds the latest few, but main block pages link to
     * their neighbours, so a longer part of the chain is cached. The heights
     * just past both of its ends are cached as unknown to the node, which is
     * true of the next one and keeps the other from asking a node too.
     */
    private function seedMainBlocks(): void
    {
        $latest = max(1, (int) config('explorer.main_blocks_count', 20));
        $cached = max($latest, self::CACHED_MAIN_BLOCKS);

        foreach ([$this->latestHeight + 1, $this->latestHeight - $cached] as $height) {
            $this->cacheBlock((string) $height, [
                'state' => 'not found', 'height' => null, 'balance' => null, 'type' => null, 'hash' => null, 'address' => null,
                'difficulty' => null, 'timestamp' => null, 'flags' => null, 'remark' => null, 'created_at' => null,
            ], []);
        }

        for ($index = 0; $index < $cached; $index++) {
            $height = $this->latestHeight - $index;
            $createdAt = $this->now->subSeconds($index * self::MAIN_BLOCK_SECONDS);
            $hash = XdagIdentifier::hash();
            $address = XdagIdentifier::addressOf($hash);
            $remark = self::POOLS[$index % count(self::POOLS)];

            if ($index < $latest) {
                DB::table('main_blocks')->insert([
                    'address' => $address,
                    'height' => $height,
                    'balance' => '64.000000000',
                    'remark' => $remark,
                    'created_at' => $this->format($createdAt),
                ]);
            }

            foreach ([$address, $hash, (string) $height] as $id) {
                $this->cacheBlock($id, [
                    'state' => 'Main',
                    'height' => $height,
                    'balance' => '64.000000000',
                    'type' => 'Main',
                    'hash' => $hash,
                    'address' => $address,
                    'difficulty' => bin2hex(random_bytes(13)),
                    'timestamp' => $this->xdagTime($createdAt),
                    'flags' => '1f',
                    'remark' => $remark,
                    'created_at' => $createdAt,
                ], [
                    ['view' => 'wallet', 'direction' => 'earning', 'address' => $address, 'amount' => '64.000000000', 'remark' => $remark, 'created_at' => $createdAt],
                ]);
            }

            $this->mainBlocks[] = $address;
        }
    }

    private function seedWalletsAndTransfers(): void
    {
        $snapshotAt = $this->now->subDays(400)->startOfDay();
        $history = [];
        $balances = [];

        for ($index = 0; $index < self::WALLETS; $index++) {
            $wallet = XdagIdentifier::walletAddress();
            $opening = random_int(30_000, 90_000).'.'.str_pad((string) random_int(0, 999_999_999), 9, '0', STR_PAD_LEFT);

            $this->wallets[] = $wallet;
            $balances[$wallet] = $opening;
            $history[$wallet] = [
                ['view' => 'wallet', 'direction' => 'snapshot', 'address' => $wallet, 'amount' => $opening, 'remark' => null, 'created_at' => $snapshotAt],
            ];
        }

        foreach ($this->transferTimes() as $time) {
            [$sender, $recipient] = $this->twoWallets();
            $amount = random_int(1, 500).'.'.str_pad((string) random_int(0, 999_999_999), 9, '0', STR_PAD_LEFT);
            $fee = '0.100000000';
            $received = bcsub($amount, $fee, 9);
            $remark = [null, null, null, 'Payment for coffee', 'Invoice 2031-17', 'see https://example.org/order'][random_int(0, 5)];
            $hash = XdagIdentifier::hash();
            $address = XdagIdentifier::addressOf($hash);

            $refs = [
                ['view' => 'transaction', 'direction' => 'input', 'address' => $sender, 'amount' => $amount, 'remark' => null, 'created_at' => null],
                ['view' => 'transaction', 'direction' => 'output', 'address' => $recipient, 'amount' => '-'.$received, 'remark' => null, 'created_at' => null],
                ['view' => 'transaction', 'direction' => 'fee', 'address' => $sender, 'amount' => '-'.$fee, 'remark' => null, 'created_at' => null],
            ];

            foreach ([$address, $hash] as $id) {
                $this->cacheBlock($id, [
                    'state' => 'Accepted',
                    'height' => 0,
                    'balance' => '0.000000000',
                    'type' => 'Transaction',
                    'hash' => $hash,
                    'address' => $address,
                    'difficulty' => bin2hex(random_bytes(13)),
                    'timestamp' => $this->xdagTime($time),
                    'flags' => '1c',
                    'remark' => $remark,
                    'created_at' => $time,
                ], $refs);
            }

            $history[$sender][] = ['view' => 'wallet', 'direction' => 'output', 'address' => $address, 'amount' => '-'.$amount, 'remark' => $remark, 'created_at' => $time];
            $history[$recipient][] = ['view' => 'wallet', 'direction' => 'input', 'address' => $address, 'amount' => $received, 'remark' => $remark, 'created_at' => $time];
            $balances[$sender] = bcsub($balances[$sender], $amount, 9);
            $balances[$recipient] = bcadd($balances[$recipient], $received, 9);

            $this->transactionBlocks[] = $address;
        }

        foreach ($this->wallets as $wallet) {
            $this->cacheBlock($wallet, [
                'state' => 'Accepted',
                'height' => 0,
                'balance' => $balances[$wallet],
                'type' => 'Wallet',
                'hash' => null,
                'address' => $wallet,
                'difficulty' => null,
                'timestamp' => $this->xdagTime($snapshotAt),
                'flags' => null,
                'remark' => null,
                'created_at' => $snapshotAt,
            ], $history[$wallet]);
        }
    }

    private function seedNotifications(): void
    {
        foreach ([
            [$this->wallets[0], 'info', 'This wallet was created by <strong>db:seed-dev</strong> for local development.'],
            [$this->transactionBlocks[0], 'warning', 'An example warning shown with a block.'],
        ] as [$id, $type, $message]) {
            DB::table('block_notifications')->insert([
                'id' => $id,
                'type' => $type,
                'timeout' => 30,
                'message' => $message,
                'created_at' => $this->format($this->now),
            ]);

            $this->notificationIds[] = $id;
        }
    }

    /**
     * Transfers happen over the last thirty days, most of them in the last
     * week, so that the weekly graphs of every wallet have something to show.
     *
     * @return array<int, CarbonImmutable>
     */
    private function transferTimes(): array
    {
        $times = [];

        for ($transfer = 0; $transfer < self::TRANSFERS; $transfer++) {
            $maxMinutes = $transfer % 3 === 0 ? 30 * 24 * 60 : 7 * 24 * 60;
            $times[] = $this->now->subMinutes(random_int(1, $maxMinutes))->addMilliseconds(random_int(0, 999));
        }

        sort($times);

        return $times;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function twoWallets(): array
    {
        $sender = random_int(0, self::WALLETS - 1);
        $recipient = ($sender + random_int(1, self::WALLETS - 1)) % self::WALLETS;

        return [$this->wallets[$sender], $this->wallets[$recipient]];
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  array<int, array<string, mixed>>  $transactions
     */
    private function cacheBlock(string $id, array $block, array $transactions): void
    {
        $this->blockRows[] = ['id' => $id] + array_merge($block, [
            'created_at' => $block['created_at'] === null ? null : $this->format($block['created_at']),
            'expires_at' => $this->format($this->now->addDays(self::CACHE_DAYS)),
        ]);

        foreach ($transactions as $transaction) {
            $this->transactionRows[] = ['block_id' => $id] + array_merge($transaction, [
                'created_at' => $transaction['created_at'] === null ? null : $this->format($transaction['created_at']),
            ]);
        }

        $this->blockIds[] = $id;
    }

    private function xdagTime(CarbonImmutable $time): string
    {
        return dechex(intdiv($time->getTimestampMs() * 1024, 1000));
    }

    private function format(CarbonImmutable $time): string
    {
        return $time->format('Y-m-d H:i:s.v');
    }
}

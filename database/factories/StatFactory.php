<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Xdag\Network\Stat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stat>
 */
class StatFactory extends Factory
{
    protected $model = Stat::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $blocks = fake()->numberBetween(4_000_000_000, 5_000_000_000);
        $mainBlocks = fake()->numberBetween(3_000_000, 4_000_000);
        $difficulty = bin2hex(random_bytes(12));
        $hashrate = fake()->numberBetween(10_000_000_000, 90_000_000_000);

        return [
            'synchronized' => true,
            'version' => '0.8.3',
            'network_type' => 'mainnet',
            'blocks' => $blocks,
            'network_blocks' => $blocks,
            'main_blocks' => $mainBlocks,
            'network_main_blocks' => $mainBlocks,
            'difficulty' => $difficulty,
            'network_difficulty' => $difficulty,
            'supply' => '1234567890.123456789',
            'network_supply' => '1234567890.123456789',
            'block_reward' => '64.000000000',
            'hashrate' => $hashrate,
            'network_hashrate' => $hashrate,
            'connections' => [
                ['nodeAddress' => fake()->ipv4().':8001', 'inBound' => fake()->numberBetween(1, 1000), 'outBound' => fake()->numberBetween(1, 1000)],
            ],
            'created_at' => now(),
        ];
    }

    public function synchronizing(): static
    {
        return $this->state(fn () => ['synchronized' => false]);
    }
}

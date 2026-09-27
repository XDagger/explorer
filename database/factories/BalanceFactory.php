<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Xdag\Block\Balance;
use App\Xdag\Block\Cache;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Balance>
 */
class BalanceFactory extends Factory
{
    protected $model = Balance::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => XdagIdentifier::walletAddress(),
            'state' => 'found',
            'balance' => fake()->numerify('####.#########'),
            'expires_at' => now()->addSeconds(Cache::TTL),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subSecond()]);
    }
}

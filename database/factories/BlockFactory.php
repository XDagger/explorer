<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Xdag\Block\Block;
use App\Xdag\Block\Cache;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Block>
 */
class BlockFactory extends Factory
{
    protected $model = Block::class;

    /**
     * A hash whose address can be requested by the test client, which drops
     * the trailing slash of a URL, so an address ending in one never reaches
     * the application intact.
     */
    public static function requestableHash(): string
    {
        do {
            $hash = XdagIdentifier::hash();
        } while (str_ends_with(XdagIdentifier::addressOf($hash), '/'));

        return $hash;
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hash = self::requestableHash();
        $address = XdagIdentifier::addressOf($hash);
        $createdAt = now()->subDays(fake()->numberBetween(1, 30))->startOfSecond()->addMilliseconds(fake()->numberBetween(0, 999));

        return [
            'id' => $address,
            'state' => 'Accepted',
            'height' => 0,
            'balance' => fake()->numerify('####.#########'),
            'type' => 'Wallet',
            'hash' => $hash,
            'address' => $address,
            'difficulty' => bin2hex(random_bytes(12)),
            'timestamp' => dechex(intdiv($createdAt->getTimestampMs() * 1024, 1000)),
            'flags' => '1c',
            'remark' => null,
            'created_at' => $createdAt,
            'expires_at' => now()->addSeconds(Cache::TTL),
        ];
    }

    public function main(): static
    {
        return $this->state(fn () => [
            'state' => 'Main',
            'type' => 'Main',
            'height' => fake()->numberBetween(3_000_000, 4_000_000),
            'balance' => '64.000000000',
            'flags' => '1f',
        ]);
    }

    public function transaction(): static
    {
        return $this->state(fn () => ['type' => 'Transaction', 'balance' => '0.000000000']);
    }

    public function byHash(): static
    {
        return $this->state(fn (array $attributes) => ['id' => $attributes['hash']]);
    }

    public function notFound(): static
    {
        return $this->state(fn () => [
            'state' => 'not found',
            'height' => null,
            'balance' => null,
            'type' => null,
            'hash' => null,
            'address' => null,
            'difficulty' => null,
            'timestamp' => null,
            'flags' => null,
            'created_at' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subSecond()]);
    }
}

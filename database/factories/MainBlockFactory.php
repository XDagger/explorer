<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Xdag\Block\MainBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MainBlock>
 */
class MainBlockFactory extends Factory
{
    protected $model = MainBlock::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'address' => XdagIdentifier::address(),
            'height' => fake()->numberBetween(3_000_000, 4_000_000),
            'balance' => '64.000000000',
            'remark' => null,
            'created_at' => now(),
        ];
    }
}

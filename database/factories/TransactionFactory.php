<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Xdag\Block\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'view' => 'wallet',
            'direction' => 'input',
            'address' => XdagIdentifier::address(),
            'amount' => fake()->numerify('###.#########'),
            'remark' => null,
            'created_at' => now()->startOfSecond(),
        ];
    }

    public function output(): static
    {
        return $this->state(fn (array $attributes) => ['direction' => 'output', 'amount' => '-'.ltrim((string) $attributes['amount'], '-')]);
    }

    public function earning(): static
    {
        return $this->state(fn () => ['direction' => 'earning']);
    }

    public function ofTransactionBlock(): static
    {
        return $this->state(fn () => ['view' => 'transaction', 'address' => XdagIdentifier::walletAddress(), 'remark' => null, 'created_at' => null]);
    }

    public function fee(): static
    {
        return $this->state(fn (array $attributes) => ['direction' => 'fee', 'amount' => '-'.ltrim((string) $attributes['amount'], '-')]);
    }
}

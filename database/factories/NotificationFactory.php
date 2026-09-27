<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Xdag\Block\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => XdagIdentifier::address(),
            'type' => 'info',
            'timeout' => 30,
            'message' => fake()->sentence(),
            'created_at' => now(),
            'show_from' => null,
            'show_to' => null,
        ];
    }
}

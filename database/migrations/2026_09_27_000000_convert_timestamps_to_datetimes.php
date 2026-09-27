<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNS = [
        'stats' => ['created_at' => 3],
        'main_blocks' => ['created_at' => 3],
        'blocks' => ['created_at' => 3, 'expires_at' => 3],
        'block_transactions' => ['created_at' => 3],
        'balances' => ['expires_at' => 3],
        'block_notifications' => ['created_at' => 0, 'show_from' => 0, 'show_to' => 0],
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            Schema::table($table, function (Blueprint $table) use ($columns) {
                foreach ($columns as $column => $precision) {
                    $table->dateTime($column, $precision)->nullable()->change();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            Schema::table($table, function (Blueprint $table) use ($columns) {
                foreach ($columns as $column => $precision) {
                    $table->timestamp($column, $precision)->nullable()->change();
                }
            });
        }
    }
};

<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\DevelopmentSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedDevelopmentData extends Command
{
    protected $signature = 'db:seed-dev {--force : Replace the data without asking}';

    protected $description = 'Replaces all explorer data with network stats, main blocks and cached blocks made up around the current time, so the explorer can be used without an XDAG node.';

    private const TABLES = ['stats', 'main_blocks', 'block_transactions', 'blocks', 'balances', 'block_notifications'];

    public function handle(DevelopmentSeeder $seeder): int
    {
        if (app()->isProduction()) {
            $this->error('Development data must never be seeded in production.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('This deletes all stats, main blocks and cached blocks in the '.DB::connection()->getDatabaseName().' database. Continue?')) {
            return self::FAILURE;
        }

        foreach (self::TABLES as $table) {
            DB::table($table)->delete();
        }

        $seeder->run();

        $this->info('Development data seeded. Cached blocks stay for 30 days, run the command again to start afresh.');
        $this->warn('Do not run the scheduler without a node: fetching the stats would mark the network as not synchronized.');
        $this->newLine();

        foreach ($seeder->summary() as $label => $path) {
            $this->line(sprintf('  %-22s %s', $label, url($path)));
        }

        return self::SUCCESS;
    }
}

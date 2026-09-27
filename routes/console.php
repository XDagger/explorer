<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('network:fetch-stats')->everyMinute()->withoutOverlapping();

Schedule::command('blocks:clear-cache')->everyMinute()->withoutOverlapping();

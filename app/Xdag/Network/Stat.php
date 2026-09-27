<?php

declare(strict_types=1);

namespace App\Xdag\Network;

use Database\Factories\StatFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(StatFactory::class)]
class Stat extends Model
{
    use HasFactory;

    protected $table = 'stats';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = ['created_at' => 'datetime', 'connections' => 'array'];
}

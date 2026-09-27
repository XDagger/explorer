<?php

declare(strict_types=1);

namespace App\Xdag\Block;

use Database\Factories\MainBlockFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(MainBlockFactory::class)]
class MainBlock extends Model
{
    use HasFactory;

    protected $table = 'main_blocks';

    protected $primaryKey = 'address';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = ['created_at' => 'datetime'];

    protected $dateFormat = 'Y-m-d H:i:s.v';

    protected $guarded = [];
}

<?php

declare(strict_types=1);

namespace App\Xdag\Block;

use App\Xdag\Exceptions\XdagException;
use Database\Factories\BalanceFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(BalanceFactory::class)]
class Balance extends Model
{
    use HasFactory;

    protected $table = 'balances';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $casts = ['expires_at' => 'datetime'];

    protected $dateFormat = 'Y-m-d H:i:s.v';

    protected $guarded = [];

    /* methods */
    public function cacheReady(): bool
    {
        return $this->state !== null;
    }

    public function blockExists(): bool
    {
        return $this->ensureCacheReady() || $this->state !== 'not found';
    }

    protected function ensureCacheReady(): void
    {
        if (! $this->cacheReady()) {
            throw new XdagException('Balance cache is not filled.');
        }
    }
}

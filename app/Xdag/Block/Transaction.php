<?php

declare(strict_types=1);

namespace App\Xdag\Block;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(TransactionFactory::class)]
class Transaction extends Model
{
    use HasFactory;

    protected $table = 'block_transactions';

    public $timestamps = false;

    protected $casts = ['created_at' => 'datetime'];

    protected $dateFormat = 'Y-m-d H:i:s.v';

    protected $guarded = [];

    /* scopes */
    public function scopeWallet($q)
    {
        return $q->whereView('wallet')->orderBy('created_at', 'desc')->orderBy('id');
    }

    public function scopeTransaction($q)
    {
        return $q->whereView('transaction')->orderBy('id');
    }

    public function scopeEarnings($q)
    {
        return $q->whereIn('direction', ['input', 'earning', 'snapshot']);
    }

    public function scopeSpendings($q)
    {
        return $q->whereIn('direction', ['output', 'fee']);
    }
}

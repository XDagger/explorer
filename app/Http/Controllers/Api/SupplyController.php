<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Xdag\Network\Stat;

class SupplyController extends Controller
{
    const TOTAL_SUPPLY = '1446294144.000000000';

    public function index()
    {
        return response()->json([
            'supply' => (int) $this->getSupply(),
        ]);
    }

    public function raw()
    {
        return response((string) (int) $this->getSupply(), 200)->header('Content-Type', 'text/plain');
    }

    public function coinGeckoWithSeparators()
    {
        return response($this->format($this->getSupply(), '.', ','), 200)->header('Content-Type', 'text/plain');
    }

    public function coinGeckoWithoutSeparators()
    {
        return response($this->format($this->getSupply(), '', ''), 200)->header('Content-Type', 'text/plain');
    }

    public function indexTotal()
    {
        return response()->json([
            'total_supply' => (int) self::TOTAL_SUPPLY,
        ]);
    }

    public function rawTotal()
    {
        return response((string) (int) self::TOTAL_SUPPLY, 200)->header('Content-Type', 'text/plain');
    }

    public function coinGeckoWithSeparatorsTotal()
    {
        return response($this->format(self::TOTAL_SUPPLY, '.', ','), 200)->header('Content-Type', 'text/plain');
    }

    public function coinGeckoWithoutSeparatorsTotal()
    {
        return response($this->format(self::TOTAL_SUPPLY, '', ''), 200)->header('Content-Type', 'text/plain');
    }

    protected function getSupply(): string
    {
        return (string) (Stat::orderBy('id', 'desc')->limit(1)->first()->supply ?? '0');
    }

    protected function format(string $amount, string $decimalSeparator, string $thousandsSeparator): string
    {
        [$integer, $fraction] = explode('.', bcadd($amount, '0', 9));

        return number_format((int) $integer, 0, '', $thousandsSeparator).$decimalSeparator.$fraction;
    }
}

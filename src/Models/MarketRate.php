<?php

namespace Wsmallnews\Wallet\Models;

use Wsmallnews\Support\Models\SupportModel;

class MarketRate extends SupportModel
{
    protected $table = 'sn_wallet_market_rates';

    protected $guarded = [];

    /**
     * 查币种对（正查：1 source = rate target）
     */
    public static function pair(string $source, string $target): ?MarketRate
    {
        return static::query()
            ->where('source_currency', strtoupper($source))
            ->where('target_currency', strtoupper($target))
            ->orderByDesc('id')
            ->first();
    }

    /**
     * 格式化展示：1 USD = 7.2 CNY
     */
    public function format(): string
    {
        return "1 {$this->source_currency} = {$this->rate} {$this->target_currency}";
    }
}

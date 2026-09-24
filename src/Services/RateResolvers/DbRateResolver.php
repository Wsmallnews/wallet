<?php

namespace Wsmallnews\Wallet\Services\RateResolvers;

use Illuminate\Database\QueryException;
use Wsmallnews\Wallet\Contracts\RateResolverInterface;
use Wsmallnews\Wallet\Support\Utils;

/**
 * 市场汇率默认实现：配置静态表优先，回落数据库表（Filament 后台维护）。
 */
class DbRateResolver implements RateResolverInterface
{
    public function rate(string $from, string $to): ?string
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === $to) {
            return '1';
        }

        $config = (array) Utils::getConfig('market_rates', []);

        if (isset($config["{$from}:{$to}"])) {
            return (string) $config["{$from}:{$to}"];
        }

        try {
            $row = Utils::getMarketRateModel()::pair($from, $to);
        } catch (QueryException) {
            return null;        // 市场汇率表未迁移
        }

        return $row ? (string) $row->rate : null;
    }
}

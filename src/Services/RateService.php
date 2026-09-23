<?php

namespace Wsmallnews\Wallet\Services;

use Wsmallnews\Wallet\Contracts\RateResolverInterface;
use Wsmallnews\Wallet\Exceptions\WalletException;
use Wsmallnews\Wallet\Models\WalletRate;
use Wsmallnews\Wallet\Models\WalletType;

/**
 * 汇率解析：钱包锚定率（钱包 ↔ 锚定币种）+ 市场汇率（法币 ↔ 法币）。
 *
 * 两跳换算模型：订单币种 --市场汇率--> 锚定币种 --锚定率--> 钱包币种。
 * 锚定率归属各钱包类型（可租户覆盖）；市场汇率以记账本位币为轴心，一维维护，不做 N×M 组合表。
 */
class RateService
{
    /**
     * 记账本位币（市场汇率表的轴心；改本位币不影响已有锚定率——锚定币种在率行上自带快照）
     */
    public function baseCurrency(): string
    {
        $configured = (string) config('sn-wallet.base_currency', '');

        return strtoupper($configured ?: sn_money()->defaultCurrency());
    }

    /**
     * 解析钱包类型的生效锚定率（租户覆盖优先，回落全局默认）
     */
    public function anchor(WalletType $type, ?int $teamId = null): WalletRate
    {
        $rate = $type->resolveRate($teamId);

        if (! $rate) {
            throw new WalletException("Wallet type [{$type->code}] has no available anchor rate.");
        }

        return $rate;
    }

    /**
     * 市场汇率：正查 → 反查取倒数；同币种恒为 1。
     *
     * @throws WalletException 双向都缺失时抛出
     */
    public function market(string $from, string $to): string
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === $to) {
            return '1';
        }

        $resolver = app(RateResolverInterface::class);

        $rate = $resolver->rate($from, $to) ?? $this->invert($resolver->rate($to, $from));

        if (! $rate) {
            throw new WalletException("Market rate [{$from} -> {$to}] is not available.");
        }

        return $rate;
    }

    /**
     * 取倒数（1 / rate），无效汇率返回 null
     */
    protected function invert(?string $rate): ?string
    {
        if (! $rate || bccomp($rate, '0', 16) === 0) {
            return null;
        }

        return bcdiv('1', $rate, 16);
    }
}

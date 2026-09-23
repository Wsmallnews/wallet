<?php

namespace Wsmallnews\Wallet\Contracts;

/**
 * 市场汇率源契约（法币间汇率，区别于钱包锚定率）。
 *
 * 默认实现读取 sn-wallet.market_rates 配置 + sn_wallet_market_rates 表（后台维护）；
 * 接外部 API 时换容器绑定即可，解析器自动支持反向取倒数。
 */
interface RateResolverInterface
{
    /**
     * 市场汇率：1 单位 $from（主单位）= ? 单位 $to（主单位）。
     *
     * @return string|null 数字字符串（bcmath），未知币种对返回 null
     */
    public function rate(string $from, string $to): ?string;
}

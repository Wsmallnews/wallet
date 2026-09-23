<?php

namespace Wsmallnews\Wallet\Services;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Wsmallnews\Wallet\Exceptions\WalletException;
use Wsmallnews\Wallet\Models\WalletType;

/**
 * 换算引擎（两跳模型，bcmath 字符串运算，不经浮点）。
 *
 *   订单币种 --市场汇率--> 锚定币种 --锚定率--> 钱包币种
 *
 * 锚定率语义：1 锚定币种主单位 = anchor_rate 个钱包主单位（如 100 积分 / 1 CNY 记 100）。
 * 本位币变更安全：锚定币种随率行快照，市场汇率轴心切换后自动多一跳换算，存量锚定不受影响。
 * 退款不重新换算：按支付时快照等比例回退（reverseBySnapshot）。
 */
class ConversionService
{
    public function __construct(protected RateService $rates) {}

    /**
     * 记账本位币
     */
    public function baseCurrency(): string
    {
        return $this->rates->baseCurrency();
    }

    /**
     * 货币金额（最小单位）→ 钱包金额（最小单位）
     *
     * @param  string  $rounding  up（扣款：远离零进位，保护平台）/ down（趋近零）/ half_up（默认）
     * @return array{wallet_amount: int, snapshot: array<string, mixed>}
     */
    public function convert(int $minorAmount, string $fromCurrency, WalletType $type, ?int $teamId = null, string $rounding = 'half_up'): array
    {
        if ($minorAmount < 0) {
            throw new WalletException('Amount cannot be negative.');
        }

        $rate = $this->rates->anchor($type, $teamId);
        $fromCurrency = strtoupper($fromCurrency);
        $anchorCurrency = strtoupper((string) $rate->anchor_currency);

        $marketRate = null;
        $anchorMinor = $minorAmount;

        if ($fromCurrency !== $anchorCurrency) {
            $marketRate = $this->rates->market($fromCurrency, $anchorCurrency);
            $anchorMinor = $this->multiplyMinor($minorAmount, $fromCurrency, $marketRate, $anchorCurrency, 'half_up');
        }

        $walletAmount = $this->applyAnchor($anchorMinor, $anchorCurrency, (string) $rate->anchor_rate, (int) $type->decimals, $rounding);

        $snapshot = [
            'order_currency' => $fromCurrency,
            'order_amount' => $minorAmount,
            'anchor_currency' => $anchorCurrency,
            'market_rate' => $marketRate,
            'anchor_rate' => (string) $rate->anchor_rate,
            'wallet_type' => $type->code,
            'wallet_decimals' => (int) $type->decimals,
            'wallet_amount' => $walletAmount,
            'rounding' => $rounding,
        ];

        return ['wallet_amount' => $walletAmount, 'snapshot' => $snapshot];
    }

    /**
     * 钱包金额（最小单位）→ 货币金额（最小单位），充值单定价用
     */
    public function convertToCurrency(int $walletAmount, WalletType $type, string $toCurrency, ?int $teamId = null): int
    {
        if ($walletAmount < 0) {
            throw new WalletException('Amount cannot be negative.');
        }

        $rate = $this->rates->anchor($type, $teamId);
        $anchorCurrency = strtoupper((string) $rate->anchor_currency);
        $toCurrency = strtoupper($toCurrency);

        // 钱包最小单位 → 锚定主单位（÷ 精度 ÷ 锚定率）→ 锚定最小单位
        $scale = bcpow('10', (string) max((int) $type->decimals, 0));
        $anchorMajor = bcdiv(bcdiv((string) $walletAmount, $scale, 20), (string) $rate->anchor_rate, 20);
        $anchorMinor = $this->roundString(bcmul($anchorMajor, bcpow('10', (string) $this->minorUnit($anchorCurrency), 20), 20), 'half_up');

        if ($anchorCurrency === $toCurrency) {
            return $anchorMinor;
        }

        return $this->multiplyMinor($anchorMinor, $anchorCurrency, $this->rates->market($anchorCurrency, $toCurrency), $toCurrency, 'half_up');
    }

    /**
     * 按支付时的快照等比例回退（退款回款，绝不重新换算）
     */
    public function reverseBySnapshot(int $refundMinor, array $snapshot): int
    {
        $orderAmount = (int) ($snapshot['order_amount'] ?? 0);
        $walletAmount = (int) ($snapshot['wallet_amount'] ?? 0);

        if ($orderAmount <= 0 || $walletAmount < 0) {
            throw new WalletException('Invalid wallet deduction snapshot.');
        }

        if ($refundMinor <= 0) {
            throw new WalletException('Refund amount must be positive.');
        }

        if ($refundMinor >= $orderAmount) {
            return $walletAmount;
        }

        return $this->roundString(bcmul(bcdiv((string) $refundMinor, (string) $orderAmount, 20), (string) $walletAmount, 20), 'half_up');
    }

    /**
     * 跨币种最小单位换算：minor(to) = minor(from) × rate × 10^(mu(to) - mu(from))
     */
    protected function multiplyMinor(int $minor, string $from, string $rate, string $to, string $rounding): int
    {
        $value = bcmul((string) $minor, $rate, 20);

        $exp = $this->minorUnit($to) - $this->minorUnit($from);
        $value = $exp >= 0
            ? bcmul($value, bcpow('10', (string) $exp, 20), 20)
            : bcdiv($value, bcpow('10', (string) -$exp, 20), 20);

        return $this->roundString($value, $rounding);
    }

    /**
     * 锚定换算：walletMinor = anchorMinor / 10^mu(anchor) × anchorRate × 10^walletDecimals
     */
    protected function applyAnchor(int $anchorMinor, string $anchorCurrency, string $anchorRate, int $walletDecimals, string $rounding): int
    {
        $value = bcdiv((string) $anchorMinor, bcpow('10', (string) $this->minorUnit($anchorCurrency), 20), 20);
        $value = bcmul($value, $anchorRate, 20);
        $value = bcmul($value, bcpow('10', (string) max($walletDecimals, 0), 20), 20);

        return $this->roundString($value, $rounding);
    }

    /**
     * ISO 4217 最小单位位数（未知币种回落 2）
     */
    protected function minorUnit(string $currency): int
    {
        try {
            return (new ISOCurrencies)->subunitFor(new Currency(strtoupper($currency)));
        } catch (\Throwable) {
            return 2;
        }
    }

    /**
     * 数字字符串取整
     *
     * @param  string  $mode  up（远离零进位）/ down（趋近零舍去）/ half_up（默认）
     */
    protected function roundString(string $value, string $mode): int
    {
        $negative = str_starts_with($value, '-');
        $abs = ltrim($value, '-');
        [$int, $frac] = array_pad(explode('.', $abs, 2), 2, '');

        $increment = match ($mode) {
            'up' => $frac !== '' && rtrim($frac, '0') !== '',
            'down' => false,
            default => ($frac === '' ? '0' : $frac[0]) >= '5',
        };

        $int = bcadd($int, $increment ? '1' : '0');

        return (int) ($negative ? '-' . $int : $int);
    }
}

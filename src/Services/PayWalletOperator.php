<?php

namespace Wsmallnews\Wallet\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Wsmallnews\Pay\Contracts\PayerInterface;
use Wsmallnews\Pay\Contracts\WalletOperator;
use Wsmallnews\Pay\Support\Utils as PayUtils;
use Wsmallnews\Wallet\Enums\TransactionType;
use Wsmallnews\Wallet\WalletManager;

/**
 * pay 包余额通道（money channel）的钱包实现。
 *
 * 两跳换算：订单币种 --市场汇率--> 锚定币种 --锚定率--> 钱包币种；
 * 扣款按 up（远离零）进位保护平台，快照随明细返回固化进 PayRecord.options.wallet；
 * 退款按支付时快照等比例回退（reverseBySnapshot），绝不重新换算。
 */
class PayWalletOperator implements WalletOperator
{
    public function __construct(
        protected WalletManager $wallets,
        protected ConversionService $conversion,
    ) {}

    /**
     * 校验钱包余额是否足够（不扣减，按扣款口径 up 进位换算）
     */
    public function sufficient(PayerInterface $payer, string $walletType, int $minorAmount, string $orderCurrency): bool
    {
        $type = $this->wallets->type($walletType);

        $converted = $this->conversion->convert($minorAmount, $orderCurrency, $type, $this->teamId($payer), 'up');

        return $this->wallets->balance($payer, $walletType) >= $converted['wallet_amount'];
    }

    /**
     * 扣减钱包（事务内调用：与支付单落库同事务）
     *
     * @param  array<string, mixed>  $meta  pay_sn / channel / method / payable{type,id}
     * @return array<string, mixed> 扣减明细（含汇率快照）
     */
    public function deduct(PayerInterface $payer, string $walletType, int $minorAmount, string $orderCurrency, array $meta = []): array
    {
        $type = $this->wallets->type($walletType);

        $converted = $this->conversion->convert($minorAmount, $orderCurrency, $type, $this->teamId($payer), 'up');
        $snapshot = $converted['snapshot'];

        $paySn = (string) ($meta['pay_sn'] ?? '');

        $transaction = $this->wallets->debit($payer, $walletType, $converted['wallet_amount'], [
            'uuid' => $paySn !== '' ? "pay:{$paySn}" : null,
            'transaction_type' => TransactionType::Consume,
            'subject' => $this->resolveSubject($meta),
            'description' => __('sn-wallet::wallet.transactions.consume_description', ['pay_sn' => $paySn]),
            'meta' => [
                'pay_sn' => $paySn,
                'channel' => (string) ($meta['channel'] ?? ''),
                'method' => (string) ($meta['method'] ?? ''),
            ],
        ]);

        return [
            'wallet_amount' => $converted['wallet_amount'],
            'wallet_currency' => $type->currency_code,
            'wallet_type' => $type->code,
            'rate' => [
                'anchor' => (string) $snapshot['anchor_rate'],
                'market' => $snapshot['market_rate'],
            ],
            'transaction_id' => $transaction->uuid,
            'snapshot' => $snapshot,
        ];
    }

    /**
     * 回款（退款）：按支付时固化在 PayRecord.options.wallet 的快照等比例回退
     *
     * @param  array<string, mixed>  $snapshot  deduct() 的完整返回（或其内层 snapshot）
     * @return array<string, mixed> 回款明细
     */
    public function credit(PayerInterface $payer, string $walletType, int $minorAmount, string $orderCurrency, array $snapshot, array $meta = []): array
    {
        $type = $this->wallets->type($walletType);

        // MoneyAdapter 传入的是 deduct() 完整返回（options.wallet），真实快照在其 snapshot 键内
        $deduction = $snapshot['snapshot'] ?? $snapshot;

        $walletAmount = $this->conversion->reverseBySnapshot($minorAmount, $deduction);

        $refundSn = (string) ($meta['refund_sn'] ?? '');

        $transaction = $this->wallets->credit($payer, $walletType, $walletAmount, [
            'uuid' => $refundSn !== '' ? "refund:{$refundSn}" : null,
            'transaction_type' => TransactionType::Refund,
            'subject' => $this->resolveRefund($refundSn),
            'description' => __('sn-wallet::wallet.transactions.refund_description', ['refund_sn' => $refundSn]),
            'meta' => [
                'refund_sn' => $refundSn,
                'pay_sn' => (string) ($meta['pay_sn'] ?? ''),
            ],
        ]);

        return [
            'wallet_amount' => $walletAmount,
            'wallet_currency' => $type->currency_code,
            'transaction_id' => $transaction->uuid,
        ];
    }

    /**
     * 钱包归属租户（Member 自带 team_id；User 无则 NULL 全局）
     */
    protected function teamId(PayerInterface $payer): ?int
    {
        return $payer->team_id ?? null;
    }

    /**
     * 从 meta 解析业务主体（订单等）：MoneyAdapter 传入 payable {type, id}
     */
    protected function resolveSubject(array $meta): ?Model
    {
        $payable = $meta['payable'] ?? null;

        if (is_array($payable) && ! empty($payable['type'])) {
            $class = Relation::getMorphedModel($payable['type']) ?: $payable['type'];

            return $class::query()->find($payable['id'] ?? 0);
        }

        return null;
    }

    /**
     * 按 refund_sn 反查退款单（退款单先于适配器调用落库）
     */
    protected function resolveRefund(string $refundSn): ?Model
    {
        if ($refundSn === '') {
            return null;
        }

        return PayUtils::getRefundModel()::query()->where('refund_sn', $refundSn)->first();
    }
}

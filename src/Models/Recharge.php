<?php

namespace Wsmallnews\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Wsmallnews\Pay\Contracts\PayableInterface;
use Wsmallnews\Pay\Support\Utils as PayUtils;
use Wsmallnews\Support\Casts\MoneyCast;
use Wsmallnews\Support\Models\SupportModel;
use Wsmallnews\Support\Support\Utils as SupportUtils;
use Wsmallnews\Wallet\Enums\RechargeStatus;
use Wsmallnews\Wallet\Enums\TransactionType;
use Wsmallnews\Wallet\Support\Utils;

/**
 * 充值单（在线充值闭环：用户下单 → pay 渠道支付 → checkAndPaid 钱包入账）。
 *
 * 实现 pay 包 PayableInterface，支付成功回调与余额通道共用同一入账路径；
 * 入账以 uuid（recharge:{id}）幂等，回调重放安全。
 */
class Recharge extends SupportModel implements PayableInterface
{
    protected $table = 'sn_wallet_recharges';

    protected $guarded = [];

    protected $casts = [
        'wallet_amount' => 'integer',
        'pay_fee' => MoneyCast::class . ':pay_currency',
        'rate_options' => 'array',
        'status' => RechargeStatus::class,
        'paid_at' => 'datetime',
    ];

    /**
     * 充值受益人（钱包所有者）
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 租户
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(SupportUtils::getTenantModel());
    }

    /**
     * 支付记录（多态）：模型经 sn-pay.models 配置解析
     */
    public function payRecords(): MorphMany
    {
        return $this->morphMany(PayUtils::getPayRecordModel(), 'payable');
    }

    /**
     * 退款单（多态）
     */
    public function payRefunds(): MorphMany
    {
        return $this->morphMany(PayUtils::getRefundModel(), 'refundable');
    }

    // ============================== PayableInterface ==============================

    public function getScopeType(): string
    {
        return Utils::getScopeable()['scope_type'];
    }

    public function getScopeId(): int
    {
        return Utils::getScopeable()['scope_id'];
    }

    /**
     * @return array{scope_type: string, scope_id: int}
     */
    public function getScopeInfo(): array
    {
        return Utils::getScopeable();
    }

    public function morphType(): string
    {
        return $this->getMorphClass();
    }

    public function morphId(): int
    {
        return (int) $this->getKey();
    }

    /**
     * @return array<string, mixed>
     */
    public function morphOptions(): array
    {
        return ['wallet_type' => $this->wallet_type, 'wallet_amount' => (int) $this->wallet_amount];
    }

    /**
     * 支付币种（创建时快照）
     */
    public function getPayCurrency(): string
    {
        return $this->pay_currency ?: sn_money()->defaultCurrency();
    }

    public function isPaid(): bool
    {
        return $this->status === RechargeStatus::Paid;
    }

    /**
     * 剩余应付金额（整数分）
     */
    public function getRemainPayFee(): int
    {
        return max(0, sn_money()->minor($this->pay_fee) - $this->getPaidFee());
    }

    /**
     * 已支付金额（整数分）
     */
    public function getPaidFee(bool $is_lock = false): int
    {
        $query = $this->payRecords()->paid();

        $is_lock && $query->lockForUpdate();

        return (int) $query->sum('real_fee');
    }

    /**
     * 全额到账时钱包入账并流转状态（幂等：已支付直接返回）
     */
    public function checkAndPaid(): Model
    {
        return DB::transaction(function () {
            $recharge = static::query()->lockForUpdate()->findOrFail($this->getKey());

            if ($recharge->isPaid()) {
                return $recharge;
            }

            if ($recharge->getPaidFee(true) >= sn_money()->minor($recharge->pay_fee)) {
                app('sn-wallet')->credit($recharge->owner, $recharge->wallet_type, (int) $recharge->wallet_amount, [
                    'uuid' => "recharge:{$recharge->id}",
                    'transaction_type' => TransactionType::Recharge,
                    'subject' => $recharge,
                    'team_id' => $recharge->team_id,
                    'description' => __('sn-wallet::wallet.recharges.credit_description'),
                ]);

                $recharge->status = RechargeStatus::Paid;
                $recharge->paid_at = now();
                $recharge->save();
            }

            return $recharge;
        });
    }
}

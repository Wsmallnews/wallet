<?php

namespace Wsmallnews\Wallet\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Wsmallnews\Support\Models\SupportModel;
use Wsmallnews\Support\Support\Utils as SupportUtils;
use Wsmallnews\Wallet\Enums\TransactionType;
use Wsmallnews\Wallet\Exceptions\WalletException;
use Wsmallnews\Wallet\Support\Utils;

class WalletTransaction extends SupportModel
{
    protected $table = 'sn_wallet_transactions';

    protected $guarded = [];

    protected $casts = [
        'type' => TransactionType::class,
        'amount' => 'integer',
        'balance_change' => 'integer',
        'frozen_change' => 'integer',
        'balance_after' => 'integer',
        'frozen_after' => 'integer',
        'options' => 'array',
    ];

    protected static function booted(): void
    {
        // 不可变账本：禁止修改与删除，冲正走反向流水
        static::updating(function () {
            throw new WalletException('Wallet transactions are immutable.');
        });

        static::deleting(function () {
            throw new WalletException('Wallet transactions are immutable.');
        });
    }

    /**
     * 所属钱包
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Utils::getWalletModel());
    }

    /**
     * 业务主体（订单 / 支付单 / 退款单 / 充值单等）
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 触发人（管理员调整时记录操作者）
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 归属租户（全局钱包的租户归因）
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(SupportUtils::getTenantModel());
    }

    public function getMorphClass(): string
    {
        return 'sn_wallet_transaction';
    }
}

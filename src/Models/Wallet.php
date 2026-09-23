<?php

namespace Wsmallnews\Wallet\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Wsmallnews\Support\Models\SupportModel;
use Wsmallnews\Support\Support\Utils as SupportUtils;
use Wsmallnews\Wallet\Support\Utils;

class Wallet extends SupportModel
{
    protected $table = 'sn_wallets';

    protected $guarded = [];

    protected $casts = [
        'balance' => 'integer',
        'frozen' => 'integer',
    ];

    /**
     * 钱包所有者（User 跨租户共享 / Member 租户内）
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 钱包类型
     */
    public function walletType(): BelongsTo
    {
        return $this->belongsTo(Utils::getWalletTypeModel(), 'wallet_type_id');
    }

    /**
     * 流水（不可变账本）
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Utils::getWalletTransactionModel())->orderBy('id', 'asc');
    }

    /**
     * 租户（NULL = 全局钱包，如跨租户共享的用户钱包）
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(SupportUtils::getTenantModel());
    }

    /**
     * 可用余额展示
     */
    public function formatBalance(): string
    {
        return $this->walletType?->format((int) $this->balance) ?? (string) $this->balance;
    }

    /**
     * 冻结金额展示
     */
    public function formatFrozen(): string
    {
        return $this->walletType?->format((int) $this->frozen) ?? (string) $this->frozen;
    }

    public function getMorphClass(): string
    {
        return 'sn_wallet';
    }
}

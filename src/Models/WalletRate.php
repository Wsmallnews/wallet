<?php

namespace Wsmallnews\Wallet\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Wsmallnews\Support\Models\SupportModel;
use Wsmallnews\Support\Support\Utils as SupportUtils;
use Wsmallnews\Wallet\Support\Utils;

class WalletRate extends SupportModel
{
    protected $table = 'sn_wallet_rates';

    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * 所属钱包类型
     */
    public function walletType(): BelongsTo
    {
        return $this->belongsTo(Utils::getWalletTypeModel());
    }

    /**
     * 租户（team_id 为 NULL 时表示全局默认锚定率）
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(SupportUtils::getTenantModel());
    }

    /**
     * 锚定率展示：如 100 POINT / 1 CNY
     */
    public function formatAnchor(): string
    {
        return trim("{$this->anchor_rate} " . $this->walletType?->currency_code . ' / 1 ' . $this->anchor_currency);
    }

    /**
     * 本表是否为全局默认行
     */
    public function isGlobal(): bool
    {
        return is_null($this->team_id);
    }
}

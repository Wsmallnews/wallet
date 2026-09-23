<?php

namespace Wsmallnews\Wallet\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Wsmallnews\Support\Models\SupportModel;
use Wsmallnews\Wallet\Support\Utils;

class WalletType extends SupportModel
{
    protected $table = 'sn_wallet_types';

    protected $guarded = [];

    protected $casts = [
        'decimals' => 'integer',
        'enabled' => 'boolean',
    ];

    /**
     * 类型下的钱包
     */
    public function wallets(): HasMany
    {
        return $this->hasMany(Utils::getWalletModel());
    }

    /**
     * 锚定率配置（全局默认 + 租户覆盖）
     */
    public function rates(): HasMany
    {
        return $this->hasMany(Utils::getWalletRateModel());
    }

    /**
     * 解析生效的锚定率：租户覆盖优先，回落全局默认
     */
    public function resolveRate(?int $teamId = null): ?WalletRate
    {
        if ($teamId) {
            $rate = $this->rates()
                ->where('team_id', $teamId)
                ->where('enabled', true)
                ->orderByDesc('id')
                ->first();

            if ($rate) {
                return $rate;
            }
        }

        return $this->rates()
            ->whereNull('team_id')
            ->where('enabled', true)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * 钱包最小单位 → 主单位字符串（bcmath，不经浮点）
     */
    public function toMajor(int $minorAmount): string
    {
        $scale = max((int) $this->decimals, 0);

        return bcdiv((string) $minorAmount, bcpow('10', (string) $scale), $scale);
    }

    /**
     * 格式化展示（主单位 + 币种代码）
     */
    public function format(int $minorAmount): string
    {
        return $this->toMajor($minorAmount) . ' ' . $this->currency_code;
    }

    /**
     * 获取类型（经 sn-wallet.models 配置解析）
     */
    public static function getType(string $typeCode, bool $shouldException = true): ?WalletType
    {
        return app('sn-wallet')->type($typeCode, $shouldException);
    }

    /**
     * 按类型查找模型（供 morph 解析等场景）
     */
    public function getMorphClass(): string
    {
        return 'sn_wallet_type';
    }
}

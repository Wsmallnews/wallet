<?php

namespace Wsmallnews\Wallet\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Wsmallnews\Wallet\Models\Wallet;
use Wsmallnews\Wallet\Support\Utils;

/**
 * 钱包所有者侧 trait（User / Member 等任意模型 use）。
 *
 * User 钱包全局共享（team_id NULL），Member 钱包挂租户（team_id 取模型属性）；
 * 写操作统一走 WalletManager（app('sn-wallet') / Wallet facade），不走模型后门。
 */
trait HasWallets
{
    /**
     * 所有类型钱包
     */
    public function wallets(): MorphMany
    {
        return $this->morphMany(Utils::getWalletModel(), 'owner');
    }

    /**
     * 某类型钱包（只读，不建行）
     */
    public function walletOfType(string $typeCode): ?Wallet
    {
        return app('sn-wallet')->wallet($this, $typeCode);
    }

    /**
     * 某类型可用余额（无记录 = 0）
     */
    public function walletBalance(string $typeCode): int
    {
        return app('sn-wallet')->balance($this, $typeCode);
    }

    /**
     * 某类型冻结金额（无记录 = 0）
     */
    public function walletFrozen(string $typeCode): int
    {
        return app('sn-wallet')->frozen($this, $typeCode);
    }
}

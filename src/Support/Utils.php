<?php

namespace Wsmallnews\Wallet\Support;

use Wsmallnews\Wallet\Exceptions\WalletException;

class Utils
{
    /**
     * 读取 sn-wallet 配置（dot notation）
     */
    public static function getConfig(?string $name, mixed $default = null): mixed
    {
        return config("sn-wallet.{$name}", $default);
    }

    /**
     * 获取配置的模型类名
     *
     * @param  string  $name  type / rate / wallet / transaction / market_rate / recharge
     */
    public static function getModel(string $name, bool $shouldException = true): ?string
    {
        $model = static::getConfig("models.{$name}", null);

        if (! $model && $shouldException) {
            throw new WalletException("Wallet model [{$name}] is not configured.");
        }

        return $model;
    }

    public static function getWalletTypeModel(): string
    {
        return static::getModel('wallet_type');
    }

    public static function getWalletRateModel(): string
    {
        return static::getModel('wallet_rate');
    }

    public static function getWalletModel(): string
    {
        return static::getModel('wallet');
    }

    public static function getWalletTransactionModel(): string
    {
        return static::getModel('wallet_transaction');
    }

    public static function getMarketRateModel(): string
    {
        return static::getModel('market_rate');
    }

    public static function getRechargeModel(): string
    {
        return static::getModel('recharge');
    }

    /**
     * 默认 scopeable（main 实例）
     *
     * @return array{scope_type: string, scope_id: int}
     */
    public static function getScopeable(): array
    {
        $main = (array) static::getConfig('scopeables.main', []);

        return [
            'scope_type' => (string) ($main['scope_type'] ?? 'sn-wallet'),
            'scope_id' => (int) ($main['scope_id'] ?? 0),
        ];
    }

    /**
     * 面板注册配置
     */
    public static function getPanelRegister(?string $type = null): mixed
    {
        $registers = (array) static::getConfig('panel_register', []);

        return is_null($type) ? $registers : ($registers[$type] ?? []);
    }
}

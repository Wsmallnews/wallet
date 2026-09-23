<?php

namespace Wsmallnews\Wallet\Facades;

use Illuminate\Support\Facades\Facade;
use Wsmallnews\Wallet\WalletManager;

/**
 * @method static void registers(array<string, array<string, mixed>> $types)
 * @method static \Wsmallnews\Wallet\Models\WalletType registerType(string $code, array $params = [])
 * @method static \Wsmallnews\Wallet\Models\WalletType type(string $typeCode, bool $shouldException = true)
 * @method static ?\Wsmallnews\Wallet\Models\Wallet wallet(\Illuminate\Database\Eloquent\Model $owner, string $typeCode)
 * @method static int balance(\Illuminate\Database\Eloquent\Model $owner, string $typeCode)
 * @method static int frozen(\Illuminate\Database\Eloquent\Model $owner, string $typeCode)
 * @method static \Wsmallnews\Wallet\Models\WalletTransaction credit(\Illuminate\Database\Eloquent\Model $owner, string $typeCode, int $amount, array $options = [])
 * @method static \Wsmallnews\Wallet\Models\WalletTransaction debit(\Illuminate\Database\Eloquent\Model $owner, string $typeCode, int $amount, array $options = [])
 * @method static \Wsmallnews\Wallet\Models\WalletTransaction freeze(\Illuminate\Database\Eloquent\Model $owner, string $typeCode, int $amount, array $options = [])
 * @method static \Wsmallnews\Wallet\Models\WalletTransaction unfreeze(\Illuminate\Database\Eloquent\Model $owner, string $typeCode, int $amount, array $options = [])
 * @method static \Wsmallnews\Wallet\Models\WalletTransaction debitFrozen(\Illuminate\Database\Eloquent\Model $owner, string $typeCode, int $amount, array $options = [])
 * @method static array transfer(\Illuminate\Database\Eloquent\Model $from, \Illuminate\Database\Eloquent\Model $to, string $typeCode, int $amount, array $options = [])
 * @method static \Wsmallnews\Wallet\Models\Recharge createRecharge(\Illuminate\Database\Eloquent\Model $owner, string $typeCode, int $walletAmount, ?string $payCurrency = null)
 *
 * @see WalletManager
 */
class Wallet extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sn-wallet';
    }
}

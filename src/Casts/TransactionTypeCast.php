<?php

namespace Wsmallnews\Wallet\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Wsmallnews\Wallet\Enums\TransactionType;

/**
 * 容错流水类型 cast：内置枚举正常转换，调用方注册的自定义类型保持字符串原样。
 */
class TransactionTypeCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        return TransactionType::tryFrom((string) $value) ?? $value;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if ($value instanceof TransactionType) {
            return $value->value;
        }

        return $value === null ? null : (string) $value;
    }
}

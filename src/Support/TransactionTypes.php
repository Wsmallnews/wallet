<?php

namespace Wsmallnews\Wallet\Support;

use Wsmallnews\Wallet\Enums\TransactionType;

/**
 * 流水类型注册表：内置 TransactionType 枚举 + 调用方注册的自定义类型。
 *
 * 自定义类型仅当需要独立筛选/统计口径时注册（如 commission）；
 * 区分业务来源用 subject morph + options 即可，不必新建类型。
 */
class TransactionTypes
{
    /**
     * 调用方注册的自定义类型
     *
     * @var array<string, array<string, mixed>>
     */
    protected static array $customs = [];

    /**
     * 批量注册自定义流水类型
     *
     * @param  array<string, array<string, mixed>>  $types  value => ['label' => ..., 'color' => ...]
     */
    public static function register(array $types): void
    {
        static::$customs = array_merge(static::$customs, $types);
    }

    /**
     * 全部类型（内置 + 自定义）
     *
     * @return array<string, string> value => label
     */
    public static function all(): array
    {
        $labels = [];

        foreach (TransactionType::cases() as $case) {
            $labels[$case->value] = (string) $case->getLabel();
        }

        foreach (static::$customs as $value => $meta) {
            $labels[$value] = (string) ($meta['label'] ?? $value);
        }

        return $labels;
    }

    public static function label(string $value): ?string
    {
        $case = TransactionType::tryFrom($value);

        return $case?->getLabel() ?? (static::$customs[$value]['label'] ?? null);
    }

    public static function color(string $value): ?string
    {
        $case = TransactionType::tryFrom($value);

        return $case?->getColor() ?? (static::$customs[$value]['color'] ?? 'gray');
    }

    /**
     * 测试隔离用：清空注册表
     */
    public static function flush(): void
    {
        static::$customs = [];
    }
}

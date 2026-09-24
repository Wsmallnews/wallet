<?php

use Wsmallnews\Wallet\Filament\Resources\MarketRates\MarketRateResource;
use Wsmallnews\Wallet\Filament\Resources\Recharges\RechargeResource;
use Wsmallnews\Wallet\Filament\Resources\Wallets\WalletResource;
use Wsmallnews\Wallet\Filament\Resources\WalletTypes\WalletTypeResource;
use Wsmallnews\Wallet\Models\MarketRate;
use Wsmallnews\Wallet\Models\Recharge;
use Wsmallnews\Wallet\Models\Wallet;
use Wsmallnews\Wallet\Models\WalletRate;
use Wsmallnews\Wallet\Models\WalletTransaction;
use Wsmallnews\Wallet\Models\WalletType;

return [

    /*
    |--------------------------------------------------------------------------
    | Scopeable 实例
    |--------------------------------------------------------------------------
    */
    'scopeables' => [
        'main' => [
            'scope_type' => 'sn-wallet',
            'scope_id' => 0,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 记账本位币
    |--------------------------------------------------------------------------
    | 市场汇率表的轴心币种。默认 null = 与站点默认货币一致（app.currency → sn-support.currency → CNY），
    | 仅当「展示默认币种 ≠ 内部记账轴心」（如面向海外默认 USD 展示、以 CNY 记账）时才需要显式配置。
    | 改本位币不影响存量锚定率——锚定币种随率行自带快照，换算时自动多一跳市场汇率。
    */
    'base_currency' => null,

    /*
    |--------------------------------------------------------------------------
    | 代码声明的钱包类型
    |--------------------------------------------------------------------------
    | 与调用方 ServiceProvider 中 Wallet::registers() 同款结构，仅作初始默认值；
    | 落库后以数据库为准（后台调整锚定率不会被重新部署覆盖）。
    |
    | 参数：name / currency_code / decimals / enabled / anchor_rate / anchor_currency
    */
    'types' => [
        // 'balance' => ['name' => '余额', 'currency_code' => 'CNY', 'decimals' => 2, 'anchor_rate' => 1, 'anchor_currency' => 'CNY'],
        // 'point' => ['name' => '积分', 'currency_code' => 'POINT', 'decimals' => 0, 'anchor_rate' => 100, 'anchor_currency' => 'CNY'],
    ],

    /*
    |--------------------------------------------------------------------------
    | 市场汇率静态兜底（优先于数据库表）
    |--------------------------------------------------------------------------
    | 'USD:CNY' => '7.2' 表示 1 USD = 7.2 CNY；正式运营建议用数据库表（后台维护）或外部 API（换 RateResolverInterface 绑定）。
    */
    'market_rates' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | 充值单
    |--------------------------------------------------------------------------
    */
    'recharge' => [
        'enabled' => true,

        // 充值支付币种；null = 记账本位币
        'currency' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | 可替换模型
    |--------------------------------------------------------------------------
    */
    'models' => [
        'wallet_type' => WalletType::class,
        'wallet_rate' => WalletRate::class,
        'wallet' => Wallet::class,
        'wallet_transaction' => WalletTransaction::class,
        'market_rate' => MarketRate::class,
        'recharge' => Recharge::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | 面板注册
    |--------------------------------------------------------------------------
    */
    'panel_register' => [
        'global_default' => [
            'navigation_group' => 'sn-wallet::wallet.global_default.navigation_group',
        ],
        'resources' => [
            WalletResource::class => [
                'navigation_sort' => 1,
            ],
            WalletTypeResource::class => [
                'navigation_sort' => 2,
            ],
            MarketRateResource::class => [
                'navigation_sort' => 3,
            ],
            RechargeResource::class => [
                'navigation_sort' => 4,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 文件目录
    |--------------------------------------------------------------------------
    */
    'file_directory' => 'sn/wallet/',

];

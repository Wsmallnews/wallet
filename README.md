# Wsmallnews Wallet

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wsmallnews/wallet.svg?style=flat-square)](https://packagist.org/packages/wsmallnews/wallet)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/wsmallnews/wallet/tests.yml?branch=v1&label=tests&style=flat-square)](https://github.com/wsmallnews/wallet/actions?query=workflow%3Atests+branch%3Av1)
[![Total Downloads](https://img.shields.io/packagist/dt/wsmallnews/wallet.svg?style=flat-square)](https://packagist.org/packages/wsmallnews/wallet)

Wsmallnews 生态的多类型虚拟钱包扩展包：余额 / 积分 / 能量值等任意钱包类型，不可变流水账本、冻结体系、钱包间转账、在线充值闭环与 pay 包余额通道集成。

## 特性

- **任意钱包类型**：调用方在 ServiceProvider 中 `Wallet::registers()` 声明（存在性归代码），锚定率等参数落库可后台运营调整（支持租户覆盖）
- **两跳换算**：订单币种 --市场汇率--> 锚定币种 --锚定率--> 钱包币种；市场汇率一维护护（以记账本位币为轴心），不做 N×M 组合表
- **不可变账本**：每笔变动一条流水（uuid 幂等键、余额/冻结双快照），禁止 update/delete，冲正走反向流水
- **并发安全**：行级锁 + 余额下限校验同事务，不允许负余额；并发同 uuid 由唯一键兜底
- **冻结体系**：佣金发放直接入冻结、售后窗口退款走冻结扣减、窗口期后解冻可提现——佣金不再变负
- **多租户归属**：`team_id NULL = 全局钱包`（User 跨租户共享），Member 钱包挂租户；流水行记租户归因
- **充值闭环**：Recharge 充值单实现 pay 包 `PayableInterface`，支付成功自动入账（幂等）
- **余额支付**：实现 pay 包 `WalletOperator` 契约，绑定即启用 `money` 通道；退款按支付时快照等比例回退，绝不重新换算
- **运维工具**：`sn-wallet:reconcile` 对账命令（重放流水校验余额，`--fix` 修复）+ Filament 管理界面（钱包调整 / 类型费率 / 市场汇率 / 充值单）

## 安装

```bash
composer require wsmallnews/wallet
```

```bash
php artisan sn-wallet:install
```

## 快速上手

```php
use Wsmallnews\Wallet\Facades\Wallet;

// 1. 调用方 ServiceProvider 中声明钱包类型（初始默认值，落库后以库内为准）
Wallet::registers([
    'balance' => ['name' => '余额', 'currency_code' => 'CNY', 'decimals' => 2, 'anchor_rate' => 1, 'anchor_currency' => 'CNY'],
    'point' => ['name' => '积分', 'currency_code' => 'POINT', 'decimals' => 0, 'anchor_rate' => 100, 'anchor_currency' => 'CNY'],
]);

// 2. 余额操作（金额一律整数最小单位）
Wallet::credit($member, 'point', 1000);                       // 入账
Wallet::debit($member, 'point', 300);                         // 扣减（余额不足抛异常）
Wallet::credit($member, 'point', 500, ['frozen' => true]);    // 冻结入账（佣金）
Wallet::unfreeze($member, 'point', 500);                      // 解冻
Wallet::debitFrozen($member, 'point', 200);                   // 冻结扣减（售后追回）
Wallet::transfer($memberA, $memberB, 'point', 100);           // 转账（双流水对冲）
Wallet::balance($member, 'point');                            // 查询（无记录 = 0）

// 3. 在线充值（下单 → pay 支付 → 自动入账）
$recharge = Wallet::createRecharge($member, 'point', 1000);   // 1000 积分，自动定价 ¥10
app('sn-pay')->payer($member)->payable($recharge)->channel('alipay', 'web')->pay();

// 4. 余额支付（启用 sn-pay.channels.money 后）
app('sn-pay')->payer($member)->payable($order)
    ->channel('money', 'balance')
    ->pay(null, ['wallet_type' => 'point']);                  // 积分支付（自动两跳换算）
```

## 换算模型

```
订单应付 $10 ──市场汇率 7.2──> ¥72 ──锚定率 100:1──> 扣 7200 积分
```

> 完整的架构讲解（ER 图 / 流程图 / 设计决策速查）见 [docs/architecture.md](docs/architecture.md)。

- 锚定率语义：`1 锚定币种主单位 = anchor_rate 个钱包主单位`（100 积分/1 元 记 100）
- 扣款 `up`（远离零）进位保护平台；退款按支付时快照等比例回退
- 改记账本位币不影响存量锚定率（锚定币种随率行自带快照，自动多一跳市场汇率）
- 市场汇率：`sn_wallet_market_rates` 表后台维护 / `sn-wallet.market_rates` 配置兜底 / 换绑定 `RateResolverInterface` 接外部 API

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

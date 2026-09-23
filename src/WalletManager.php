<?php

namespace Wsmallnews\Wallet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Wsmallnews\Wallet\Enums\TransactionType;
use Wsmallnews\Wallet\Exceptions\WalletException;
use Wsmallnews\Wallet\Models\Recharge;
use Wsmallnews\Wallet\Models\Wallet;
use Wsmallnews\Wallet\Models\WalletTransaction;
use Wsmallnews\Wallet\Models\WalletType;
use Wsmallnews\Wallet\Services\ConversionService;
use Wsmallnews\Wallet\Support\Utils;

/**
 * 钱包管理器（app('sn-wallet') / Wallet facade）。
 *
 * 账本约定：
 *  - 金额一律整数最小单位（钱包类型自带精度 decimals），与 sn_money() 的分口径一致
 *  - 每笔变动一条不可变流水（uuid 幂等键），余额更新与流水写入同事务
 *  - 行级锁 + 余额下限校验，不允许负余额（可用与冻结均 >= 0）
 *  - 钱包懒创建：每所有者每类型一条记录，读路径不建行（absent = 0）
 *  - team_id 归属：owner 自带 team_id 属性（Member）则挂租户，否则 NULL 全局（User 跨租户共享）
 */
class WalletManager
{
    /**
     * 代码声明的钱包类型（延迟落库：首次解析时 upsert，避免 provider boot 期访问数据库）
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $declaredTypes = [];

    public function __construct(protected ConversionService $conversion)
    {
        $this->declaredTypes = (array) config('sn-wallet.types', []);
    }

    // ============================== 类型注册 ==============================

    /**
     * 批量声明钱包类型（调用方 ServiceProvider 中调用，如 shop 声明 balance / point）。
     *
     * 代码声明是类型的初始默认值；落库后以数据库为准（后台改锚定率不会被重新部署覆盖）。
     *
     * @param  array<string, array<string, mixed>>  $types  code => 参数
     */
    public function registers(array $types): void
    {
        $this->declaredTypes = array_merge($this->declaredTypes, $types);
    }

    /**
     * 声明（并立即落库）单个钱包类型
     *
     * @param  array<string, mixed>  $params  name / currency_code / decimals / enabled / anchor_rate / anchor_currency
     */
    public function registerType(string $code, array $params = []): WalletType
    {
        return DB::transaction(function () use ($code, $params) {
            $type = Utils::getWalletTypeModel()::updateOrCreate(
                ['code' => $code],
                [
                    'name' => (string) ($params['name'] ?? $code),
                    'currency_code' => strtoupper((string) ($params['currency_code'] ?? $this->conversion->baseCurrency())),
                    'decimals' => (int) ($params['decimals'] ?? 2),
                    'enabled' => (bool) ($params['enabled'] ?? true),
                ],
            );

            if (isset($params['anchor_rate'])) {
                Utils::getWalletRateModel()::updateOrCreate(
                    ['wallet_type_id' => $type->id, 'team_id' => null],
                    [
                        'anchor_rate' => (string) $params['anchor_rate'],
                        'anchor_currency' => strtoupper((string) ($params['anchor_currency'] ?? $this->conversion->baseCurrency())),
                        'enabled' => true,
                    ],
                );
            }

            return $type;
        });
    }

    // ============================== 解析 ==============================

    /**
     * 解析钱包类型（代码声明未落库时自动 upsert）
     */
    public function type(string $typeCode, bool $shouldException = true): ?WalletType
    {
        $type = Utils::getWalletTypeModel()::query()->where('code', $typeCode)->first();

        if (! $type && isset($this->declaredTypes[$typeCode])) {
            $type = $this->registerType($typeCode, (array) $this->declaredTypes[$typeCode]);
        }

        if (! $type && $shouldException) {
            throw new WalletException("Wallet type [{$typeCode}] is not registered.");
        }

        return $type;
    }

    /**
     * 所有者的某类型钱包（只读，不建行）
     */
    public function wallet(Model $owner, string $typeCode): ?Wallet
    {
        $type = $this->type($typeCode);

        return Utils::getWalletModel()::query()
            ->where('owner_type', $owner->getMorphClass())
            ->where('owner_id', $owner->getKey())
            ->where('wallet_type_id', $type->id)
            ->first();
    }

    /**
     * 可用余额（无记录 = 0）
     */
    public function balance(Model $owner, string $typeCode): int
    {
        return (int) ($this->wallet($owner, $typeCode)?->balance ?? 0);
    }

    /**
     * 冻结金额（无记录 = 0）
     */
    public function frozen(Model $owner, string $typeCode): int
    {
        return (int) ($this->wallet($owner, $typeCode)?->frozen ?? 0);
    }

    // ============================== 核心变动 ==============================

    /**
     * 入账（frozen=true 直接入冻结，佣金发放等售后窗口场景）
     *
     * @param  array<string, mixed>  $options  uuid / frozen / transaction_type / subject / causer / description / team_id / meta / transfer_sn
     */
    public function credit(Model $owner, string $typeCode, int $amount, array $options = []): WalletTransaction
    {
        $this->assertPositive($amount);

        $type = $this->type($typeCode);
        $wallet = $this->resolveOrCreateWallet($owner, $type);

        $toFrozen = (bool) ($options['frozen'] ?? false);
        $transactionType = $options['transaction_type'] ?? ($toFrozen ? TransactionType::FrozenCredit : TransactionType::Recharge);

        return $this->mutate($wallet, $transactionType, $amount, $toFrozen ? 0 : $amount, $toFrozen ? $amount : 0, $options);
    }

    /**
     * 扣减可用余额（余额不足抛异常）
     */
    public function debit(Model $owner, string $typeCode, int $amount, array $options = []): WalletTransaction
    {
        $this->assertPositive($amount);

        $type = $this->type($typeCode);
        $wallet = $this->resolveOrCreateWallet($owner, $type);

        $transactionType = $options['transaction_type'] ?? TransactionType::Consume;

        return $this->mutate($wallet, $transactionType, -$amount, -$amount, 0, $options);
    }

    /**
     * 冻结：可用 → 冻结（佣金提现风控 / 售后窗口锁定）
     */
    public function freeze(Model $owner, string $typeCode, int $amount, array $options = []): WalletTransaction
    {
        $this->assertPositive($amount);

        $type = $this->type($typeCode);
        $wallet = $this->resolveOrCreateWallet($owner, $type);

        return $this->mutate($wallet, TransactionType::Freeze, -$amount, -$amount, $amount, $options);
    }

    /**
     * 解冻：冻结 → 可用（订单确认收货后佣金可提现）
     */
    public function unfreeze(Model $owner, string $typeCode, int $amount, array $options = []): WalletTransaction
    {
        $this->assertPositive($amount);

        $type = $this->type($typeCode);
        $wallet = $this->resolveOrCreateWallet($owner, $type);

        return $this->mutate($wallet, TransactionType::Unfreeze, $amount, $amount, -$amount, $options);
    }

    /**
     * 冻结扣减：直接消耗冻结金额（售后窗口内订单退款，佣金追回）
     */
    public function debitFrozen(Model $owner, string $typeCode, int $amount, array $options = []): WalletTransaction
    {
        $this->assertPositive($amount);

        $type = $this->type($typeCode);
        $wallet = $this->resolveOrCreateWallet($owner, $type);

        return $this->mutate($wallet, TransactionType::FreezeConsume, -$amount, 0, -$amount, $options);
    }

    /**
     * 钱包间转账（同类型；事务内双流水，transfer_sn 关联对冲；固定顺序加锁防死锁）
     *
     * @return array{out: WalletTransaction, in: WalletTransaction}
     */
    public function transfer(Model $from, Model $to, string $typeCode, int $amount, array $options = []): array
    {
        $this->assertPositive($amount);

        $type = $this->type($typeCode);
        $fromWallet = $this->resolveOrCreateWallet($from, $type);
        $toWallet = $this->resolveOrCreateWallet($to, $type);

        $transferSn = (string) ($options['transfer_sn'] ?? get_sn('wallet', 'T'));
        $options['transfer_sn'] = $transferSn;

        $outUuid = "transfer-out:{$transferSn}";
        $inUuid = "transfer-in:{$transferSn}";

        try {
            return DB::transaction(function () use ($fromWallet, $toWallet, $amount, $options, $outUuid, $inUuid) {
                $outExisting = Utils::getWalletTransactionModel()::query()->where('uuid', $outUuid)->lockForUpdate()->first();

                if ($outExisting) {
                    $inExisting = Utils::getWalletTransactionModel()::query()->where('uuid', $inUuid)->firstOrFail();

                    return ['out' => $outExisting, 'in' => $inExisting];        // 幂等重放
                }

                // 主键升序加锁，防死锁
                $ids = collect([$fromWallet->id, $toWallet->id])->unique()->sort()->values()->all();
                $locked = Utils::getWalletModel()::query()->whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');
                $fromLocked = $locked[$fromWallet->id];
                $toLocked = $locked[$toWallet->id];

                if ($fromLocked->balance < $amount) {
                    throw new WalletException('Insufficient available balance.');
                }

                $out = $this->applyChange($fromLocked, TransactionType::TransferOut, -$amount, -$amount, 0, $options, $outUuid);
                $in = $this->applyChange($toLocked, TransactionType::TransferIn, $amount, $amount, 0, $options, $inUuid);

                return ['out' => $out, 'in' => $in];
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateError($e)) {
                $out = Utils::getWalletTransactionModel()::query()->where('uuid', $outUuid)->firstOrFail();
                $in = Utils::getWalletTransactionModel()::query()->where('uuid', $inUuid)->firstOrFail();

                return ['out' => $out, 'in' => $in];
            }

            throw $e;
        }
    }

    // ============================== 充值单 ==============================

    /**
     * 创建充值单（在线充值：下单 → pay 渠道支付 → checkAndPaid 入账）
     *
     * 应付金额按「钱包金额 → 锚定币种 → 支付币种」逆向换算，创建时快照。
     */
    public function createRecharge(Model $owner, string $typeCode, int $walletAmount, ?string $payCurrency = null): Recharge
    {
        $this->assertPositive($walletAmount);

        $type = $this->type($typeCode);
        $teamId = $owner->team_id ?? null;

        $payCurrency = strtoupper((string) ($payCurrency ?? config('sn-wallet.recharge.currency') ?: $this->conversion->baseCurrency()));
        $payFee = $this->conversion->convertToCurrency($walletAmount, $type, $payCurrency, $teamId);

        $rate = $type->resolveRate($teamId);

        return Utils::getRechargeModel()::create([
            'team_id' => $teamId,
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => $owner->getKey(),
            'wallet_type' => $type->code,
            'wallet_amount' => $walletAmount,
            'pay_currency' => $payCurrency,
            'pay_fee' => sn_money()->fromMinor($payFee, $payCurrency),
            'rate_options' => $rate ? [
                'anchor_rate' => (string) $rate->anchor_rate,
                'anchor_currency' => strtoupper((string) $rate->anchor_currency),
                'wallet_decimals' => (int) $type->decimals,
            ] : [],
            'status' => Enums\RechargeStatus::Unpaid,
        ]);
    }

    // ============================== 对账 ==============================

    /**
     * 对账：重放流水验证快照链与钱包行余额一致性
     *
     * @return array{wallets: int, mismatched: array<int, array<string, int>>}
     */
    public function reconcile(): array
    {
        $mismatched = [];
        $count = 0;

        Utils::getWalletModel()::query()->chunkById(100, function ($wallets) use (&$mismatched, &$count) {
            foreach ($wallets as $wallet) {
                $count++;

                $balance = 0;
                $frozen = 0;
                $chainOk = true;

                Utils::getWalletTransactionModel()::query()
                    ->where('wallet_id', $wallet->id)
                    ->orderBy('id')
                    ->chunkById(500, function ($transactions) use (&$balance, &$frozen, &$chainOk) {
                        foreach ($transactions as $transaction) {
                            $balance += (int) $transaction->balance_change;
                            $frozen += (int) $transaction->frozen_change;

                            if ((int) $transaction->balance_after !== $balance || (int) $transaction->frozen_after !== $frozen) {
                                $chainOk = false;
                            }
                        }
                    });

                if (! $chainOk || $balance !== (int) $wallet->balance || $frozen !== (int) $wallet->frozen) {
                    $mismatched[] = [
                        'wallet_id' => (int) $wallet->id,
                        'ledger_balance' => $balance,
                        'ledger_frozen' => $frozen,
                        'wallet_balance' => (int) $wallet->balance,
                        'wallet_frozen' => (int) $wallet->frozen,
                    ];
                }
            }
        });

        return ['wallets' => $count, 'mismatched' => $mismatched];
    }

    // ============================== 内部 ==============================

    /**
     * 单钱包原子变动：uuid 幂等 → 行锁 → 下限校验 → 余额 + 流水同事务
     */
    protected function mutate(Wallet $wallet, TransactionType $type, int $amount, int $balanceChange, int $frozenChange, array $options): WalletTransaction
    {
        $uuid = (string) ($options['uuid'] ?? Str::uuid()->toString());

        try {
            return DB::transaction(function () use ($wallet, $type, $amount, $balanceChange, $frozenChange, $options, $uuid) {
                if ($existing = Utils::getWalletTransactionModel()::query()->where('uuid', $uuid)->lockForUpdate()->first()) {
                    return $existing;        // 幂等重放
                }

                $locked = Utils::getWalletModel()::query()->lockForUpdate()->findOrFail($wallet->id);

                if ($locked->balance + $balanceChange < 0) {
                    throw new WalletException('Insufficient available balance.');
                }

                if ($locked->frozen + $frozenChange < 0) {
                    throw new WalletException('Insufficient frozen balance.');
                }

                return $this->applyChange($locked, $type, $amount, $balanceChange, $frozenChange, $options, $uuid);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateError($e)) {
                // 并发同 uuid：唯一键兜底，返回既有流水（事务已回滚，余额未受影响）
                return Utils::getWalletTransactionModel()::query()->where('uuid', $uuid)->firstOrFail();
            }

            throw $e;
        }
    }

    /**
     * 落余额与流水（须在事务 + 行锁内调用）
     */
    protected function applyChange(Wallet $wallet, TransactionType $type, int $amount, int $balanceChange, int $frozenChange, array $options, ?string $uuid = null): WalletTransaction
    {
        $wallet->balance += $balanceChange;
        $wallet->frozen += $frozenChange;
        $wallet->save();

        $subject = $options['subject'] ?? null;
        $causer = $options['causer'] ?? null;

        return Utils::getWalletTransactionModel()::create([
            'uuid' => $uuid ?? (string) Str::uuid(),
            'wallet_id' => $wallet->id,
            'team_id' => $options['team_id'] ?? (current_tenant()?->getKey() ?? $wallet->team_id),
            'type' => $type,
            'amount' => $amount,
            'balance_change' => $balanceChange,
            'frozen_change' => $frozenChange,
            'balance_after' => $wallet->balance,
            'frozen_after' => $wallet->frozen,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject ? $subject->getKey() : null,
            'causer_type' => $causer ? $causer->getMorphClass() : null,
            'causer_id' => $causer ? $causer->getKey() : null,
            'transfer_sn' => $options['transfer_sn'] ?? null,
            'description' => (string) ($options['description'] ?? ''),
            'options' => $options['meta'] ?? [],
        ]);
    }

    /**
     * 懒创建钱包（唯一键 + 冲突重取，防并发双建）
     */
    protected function resolveOrCreateWallet(Model $owner, WalletType $type): Wallet
    {
        $query = Utils::getWalletModel()::query()
            ->where('owner_type', $owner->getMorphClass())
            ->where('owner_id', $owner->getKey())
            ->where('wallet_type_id', $type->id);

        if ($wallet = $query->first()) {
            return $wallet;
        }

        $teamId = $owner->team_id ?? null;

        try {
            return Utils::getWalletModel()::create([
                'owner_type' => $owner->getMorphClass(),
                'owner_id' => $owner->getKey(),
                'wallet_type_id' => $type->id,
                'team_id' => $teamId,
            ]);
        } catch (QueryException) {
            return $query->firstOrFail();       // 并发双建兜底
        }
    }

    protected function assertPositive(int $amount): void
    {
        if ($amount <= 0) {
            throw new WalletException('Amount must be positive.');
        }
    }

    protected function isDuplicateError(QueryException $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, 'Duplicate entry') || str_contains($message, 'UNIQUE constraint failed');
    }
}

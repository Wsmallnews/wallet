<?php

namespace Wsmallnews\Wallet\Commands;

use Illuminate\Console\Command;
use Wsmallnews\Wallet\Support\Utils;
use Wsmallnews\Wallet\WalletManager;

/**
 * 钱包对账：重放流水验证快照链与钱包行余额一致性。
 *
 * php artisan sn-wallet:reconcile          # 只校验，报告差异
 * php artisan sn-wallet:reconcile --fix     # 按流水修正钱包行余额（以账本为准）
 */
class WalletReconcileCommand extends Command
{
    protected $signature = 'sn-wallet:reconcile {--fix : Fix wallet balances from the ledger}';

    protected $description = 'Reconcile wallet balances against the immutable transaction ledger';

    public function handle(): int
    {
        /** @var WalletManager $wallets */
        $wallets = app('sn-wallet');

        $report = $wallets->reconcile();

        $mismatched = $report['mismatched'];

        if ($mismatched === []) {
            $this->components->info("All {$report['wallets']} wallets reconciled. No discrepancies found.");

            return self::SUCCESS;
        }

        $this->components->warn('Found ' . count($mismatched) . ' mismatched wallets:');

        $this->table(
            ['Wallet ID', 'Ledger Balance', 'Ledger Frozen', 'Wallet Balance', 'Wallet Frozen'],
            array_map(fn ($row) => [
                $row['wallet_id'],
                $row['ledger_balance'],
                $row['ledger_frozen'],
                $row['wallet_balance'],
                $row['wallet_frozen'],
            ], $mismatched),
        );

        if ($this->option('fix')) {
            $fixed = 0;

            foreach ($mismatched as $row) {
                Utils::getWalletModel()::query()
                    ->where('id', $row['wallet_id'])
                    ->update([
                        'balance' => max(0, $row['ledger_balance']),
                        'frozen' => max(0, $row['ledger_frozen']),
                    ]);

                $fixed++;
            }

            $this->components->info("Fixed {$fixed} wallets from the ledger.");
        } else {
            $this->components->info('Run with --fix to repair wallet balances from the ledger.');
        }

        return self::SUCCESS;
    }
}

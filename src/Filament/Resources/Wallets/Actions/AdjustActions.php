<?php

namespace Wsmallnews\Wallet\Filament\Resources\Wallets\Actions;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Wsmallnews\Wallet\Enums\TransactionType;
use Wsmallnews\Wallet\Models\Wallet;
use Wsmallnews\Wallet\WalletManager;

/**
 * 钱包调整操作（管理员充值/扣除/冻结/解冻）。
 *
 * 统一走 WalletManager（余额与流水同事务 + causer 归因），不走模型后门直改余额。
 */
class AdjustActions
{
    /**
     * @return array<Action>
     */
    public static function actions(): array
    {
        return [
            static::creditAction(),
            static::debitAction(),
            static::freezeAction(),
            static::unfreezeAction(),
        ];
    }

    public static function creditAction(): Action
    {
        return static::makeAdjustAction('wallet_credit', 'credit', 'success', Heroicon::OutlinedPlusCircle);
    }

    public static function debitAction(): Action
    {
        return static::makeAdjustAction('wallet_debit', 'debit', 'danger', Heroicon::OutlinedMinusCircle);
    }

    public static function freezeAction(): Action
    {
        return static::makeAdjustAction('wallet_freeze', 'freeze', 'warning', Heroicon::OutlinedLockClosed);
    }

    public static function unfreezeAction(): Action
    {
        return static::makeAdjustAction('wallet_unfreeze', 'unfreeze', 'primary', Heroicon::OutlinedLockOpen);
    }

    protected static function makeAdjustAction(string $name, string $method, string $color, Heroicon $icon): Action
    {
        return Action::make($name)
            ->label(__('sn-wallet::wallet.wallets.actions.' . $method))
            ->icon($icon)
            ->color($color)
            ->form(fn (Wallet $record): array => [
                TextInput::make('amount')
                    ->label(__('sn-wallet::wallet.wallets.actions.amount'))
                    ->helperText($record->walletType?->format(0))
                    ->numeric()
                    ->step(((int) ($record->walletType?->decimals ?? 2)) > 0 ? 1 / (10 ** (int) $record->walletType->decimals) : 1)
                    ->minValue(0)
                    ->required()
                    ->rules(['regex:/^\d{1,12}(\.\d{1,' . ((int) ($record->walletType?->decimals ?? 2)) . '})?$/']),
                Textarea::make('description')
                    ->label(__('sn-wallet::wallet.wallets.actions.description'))
                    ->placeholder(__('sn-wallet::wallet.wallets.actions.description_placeholder'))
                    ->required()
                    ->maxLength(200),
            ])
            ->action(function (array $data, Action $action) use ($method): void {
                /** @var Wallet $record */
                $record = $action->getRecord();

                $decimals = max((int) ($record->walletType?->decimals ?? 0), 0);
                $minor = (int) bcmul((string) $data['amount'], bcpow('10', (string) $decimals));

                $options = [
                    'transaction_type' => TransactionType::Adjust,
                    'causer' => Filament::auth()->user(),
                    'description' => (string) $data['description'],
                    'team_id' => $record->team_id,
                ];

                /** @var WalletManager $wallets */
                $wallets = app('sn-wallet');

                match ($method) {
                    'credit' => $wallets->credit($record->owner, $record->walletType->code, $minor, $options),
                    'debit' => $wallets->debit($record->owner, $record->walletType->code, $minor, $options),
                    'freeze' => $wallets->freeze($record->owner, $record->walletType->code, $minor, $options),
                    'unfreeze' => $wallets->unfreeze($record->owner, $record->walletType->code, $minor, $options),
                };

                Notification::make()
                    ->success()
                    ->title(__('sn-wallet::wallet.wallets.actions.' . $method . '_success'))
                    ->send();
            });
    }

    /**
     * 供 List 页行操作复用
     *
     * @return array<Action>
     */
    public static function recordActions(): array
    {
        return static::actions();
    }
}

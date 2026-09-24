<?php

namespace Wsmallnews\Wallet\Filament\Resources\Wallets\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Wsmallnews\Support\Filament\Filters\FilterComponents;
use Wsmallnews\Wallet\Support\TransactionTypes;

/**
 * 钱包流水（只读：不可变账本，无新增/编辑/删除入口）。
 */
class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('uuid')
            ->heading(__('sn-wallet::wallet.wallets.transactions_relation.title'))
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with(['wallet.walletType', 'subject', 'causer']))
            ->columns([
                TextColumn::make('uuid')
                    ->label(__('sn-wallet::wallet.wallets.transactions_relation.uuid'))
                    ->limit(16)
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('type')
                    ->label(__('sn-wallet::wallet.wallets.transactions_relation.type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => TransactionTypes::label((string) $state) ?? (string) $state)
                    ->color(fn ($state) => TransactionTypes::color((string) $state)),
                TextColumn::make('amount')
                    ->label(__('sn-wallet::wallet.wallets.transactions_relation.amount'))
                    ->formatStateUsing(fn ($record, $state) => (($state < 0 ? '-' : '+') . $record->wallet?->walletType?->format(abs((int) $state))))
                    ->alignCenter(),
                TextColumn::make('balance_after')
                    ->label(__('sn-wallet::wallet.wallets.transactions_relation.balance_after'))
                    ->formatStateUsing(fn ($record, $state) => $record->wallet?->walletType?->format((int) $state))
                    ->alignCenter()
                    ->toggleable(),
                TextColumn::make('frozen_after')
                    ->label(__('sn-wallet::wallet.wallets.transactions_relation.frozen_after'))
                    ->formatStateUsing(fn ($record, $state) => $record->wallet?->walletType?->format((int) $state))
                    ->alignCenter()
                    ->toggleable(),
                TextColumn::make('subject')
                    ->label('Subject')
                    ->formatStateUsing(fn ($record) => $record->subject_type ? "{$record->subject_type}#{$record->subject_id}" : '—')
                    ->toggleable(),
                TextColumn::make('causer')
                    ->label('Causer')
                    ->formatStateUsing(fn ($record) => $record->causer?->getSnName() ?? ($record->causer_type ? "{$record->causer_type}#{$record->causer_id}" : '—'))
                    ->toggleable(),
                TextColumn::make('description')
                    ->label(__('sn-wallet::wallet.wallets.transactions_relation.description'))
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('sn-wallet::wallet.wallets.transactions_relation.created_at')),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('sn-wallet::wallet.wallets.transactions_relation.type'))
                    ->options(TransactionTypes::all())
                    ->multiple(),
                ...FilterComponents::createUpdateRangeFilter(),
            ]);
    }
}

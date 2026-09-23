<?php

namespace Wsmallnews\Wallet\Filament\Resources\Wallets\Tables;

use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Wsmallnews\Support\Filament\Actions\ActionComponents;
use Wsmallnews\Support\Filament\Filters\FilterComponents;
use Wsmallnews\Support\Filament\Tables\ColumnComponents;
use Wsmallnews\Wallet\Support\Utils;

class WalletsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                static::idColumn(),
                static::ownerColumn(),
                static::walletTypeColumn(),
                static::balanceColumn(),
                static::frozenColumn(),
                static::createdAtColumn(),
                static::updatedAtColumn(),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with(['walletType', 'owner']))
            ->defaultSort('id', 'desc')
            ->searchPlaceholder(__('sn-wallet::wallet.wallets.wallet_table.search_placeholder'))
            ->filtersFormWidth(Width::Medium)
            ->filters([
                static::walletTypeFilter(),
                ...FilterComponents::createUpdateRangeFilter(),
            ])
            ->recordActions([
                ...ActionComponents::recordActions([
                    ViewAction::make(),
                ]),
            ])
            ->toolbarActions([])
            ->striped();
    }

    // ========================= Columns =========================

    protected static function idColumn(): TextColumn
    {
        return TextColumn::make('id')
            ->label('ID')
            ->searchable()
            ->sortable()
            ->alignCenter()
            ->toggleable();
    }

    protected static function ownerColumn(): TextColumn
    {
        return ColumnComponents::morphColumn(
            'owner_type',
            __('sn-wallet::wallet.wallets.wallet_table.owner'),
            fn ($record) => $record->owner,
            fn ($record) => $record->owner_type,
            fn ($record) => $record->owner_id,
        );
    }

    protected static function walletTypeColumn(): TextColumn
    {
        return TextColumn::make('walletType.name')
            ->label(__('sn-wallet::wallet.wallets.wallet_table.wallet_type'))
            ->formatStateUsing(fn ($record) => "{$record->walletType?->name}（{$record->walletType?->code}）")
            ->badge()
            ->toggleable();
    }

    protected static function balanceColumn(): TextColumn
    {
        return TextColumn::make('balance')
            ->label(__('sn-wallet::wallet.wallets.wallet_table.balance'))
            ->formatStateUsing(fn ($record) => $record->formatBalance())
            ->sortable()
            ->alignCenter()
            ->toggleable();
    }

    protected static function frozenColumn(): TextColumn
    {
        return TextColumn::make('frozen')
            ->label(__('sn-wallet::wallet.wallets.wallet_table.frozen'))
            ->formatStateUsing(fn ($record) => $record->formatFrozen())
            ->sortable()
            ->alignCenter()
            ->toggleable();
    }

    protected static function createdAtColumn(): TextColumn
    {
        return TextColumn::make('created_at')
            ->label(__('sn-wallet::wallet.wallets.wallet_table.created_at'))
            ->sortable()
            ->toggleable();
    }

    protected static function updatedAtColumn(): TextColumn
    {
        return TextColumn::make('updated_at')
            ->label(__('sn-wallet::wallet.wallets.wallet_table.updated_at'))
            ->sortable()
            ->toggleable()
            ->toggledHiddenByDefault();
    }

    // ========================= Filters =========================

    protected static function walletTypeFilter(): SelectFilter
    {
        return SelectFilter::make('wallet_type_id')
            ->label(__('sn-wallet::wallet.wallets.wallet_table.wallet_type'))
            ->options(fn () => Utils::getWalletTypeModel()::query()->pluck('name', 'id')->all());
    }
}

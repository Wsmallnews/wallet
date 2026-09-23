<?php

namespace Wsmallnews\Wallet\Filament\Resources\WalletTypes\Tables;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Wsmallnews\Support\Filament\Actions\ActionComponents;
use Wsmallnews\Support\Filament\Filters\FilterComponents;

class WalletTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                static::idColumn(),
                static::codeColumn(),
                static::nameColumn(),
                static::currencyCodeColumn(),
                static::decimalsColumn(),
                static::anchorRateColumn(),
                static::enabledColumn(),
                static::createdAtColumn(),
                static::updatedAtColumn(),
            ])
            ->defaultSort('id', 'asc')
            ->searchPlaceholder(__('sn-wallet::wallet.wallet_types.wallet_type_table.search_placeholder'))
            ->filtersFormWidth(Width::Medium)
            ->filters([
                ...FilterComponents::createUpdateRangeFilter(),
            ])
            ->recordActions([
                ...ActionComponents::recordActions([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                ...ActionComponents::toolbarActions([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ])
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

    protected static function codeColumn(): TextColumn
    {
        return TextColumn::make('code')
            ->label(__('sn-wallet::wallet.wallet_types.wallet_type_table.code'))
            ->searchable()
            ->badge()
            ->copyable();
    }

    protected static function nameColumn(): TextColumn
    {
        return TextColumn::make('name')
            ->label(__('sn-wallet::wallet.wallet_types.wallet_type_table.name'))
            ->searchable();
    }

    protected static function currencyCodeColumn(): TextColumn
    {
        return TextColumn::make('currency_code')
            ->label(__('sn-wallet::wallet.wallet_types.wallet_type_table.currency_code'))
            ->badge()
            ->alignCenter();
    }

    protected static function decimalsColumn(): TextColumn
    {
        return TextColumn::make('decimals')
            ->label(__('sn-wallet::wallet.wallet_types.wallet_type_table.decimals'))
            ->alignCenter()
            ->toggleable();
    }

    protected static function anchorRateColumn(): TextColumn
    {
        return TextColumn::make('anchor_rate')
            ->label(__('sn-wallet::wallet.wallet_types.wallet_type_table.anchor_rate'))
            ->state(fn ($record) => $record->resolveRate()?->formatAnchor() ?? '—')
            ->alignCenter()
            ->toggleable();
    }

    protected static function enabledColumn(): IconColumn
    {
        return IconColumn::make('enabled')
            ->label(__('sn-wallet::wallet.wallet_types.wallet_type_table.enabled'))
            ->boolean()
            ->alignCenter();
    }

    protected static function createdAtColumn(): TextColumn
    {
        return TextColumn::make('created_at')
            ->label(__('sn-wallet::wallet.wallet_types.wallet_type_table.created_at'))
            ->sortable()
            ->toggleable();
    }

    protected static function updatedAtColumn(): TextColumn
    {
        return TextColumn::make('updated_at')
            ->label(__('sn-wallet::wallet.wallet_types.wallet_type_table.updated_at'))
            ->sortable()
            ->toggleable()
            ->toggledHiddenByDefault();
    }
}

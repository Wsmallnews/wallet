<?php

namespace Wsmallnews\Wallet\Filament\Resources\MarketRates\Tables;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Wsmallnews\Support\Filament\Actions\ActionComponents;
use Wsmallnews\Support\Filament\Filters\FilterComponents;

class MarketRatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                static::idColumn(),
                static::sourceCurrencyColumn(),
                static::targetCurrencyColumn(),
                static::rateColumn(),
                static::createdAtColumn(),
                static::updatedAtColumn(),
            ])
            ->defaultSort('id', 'asc')
            ->searchPlaceholder(__('sn-wallet::wallet.market_rates.market_rate_table.search_placeholder'))
            ->filtersFormWidth(Width::Medium)
            ->filters([
                ...FilterComponents::createUpdateRangeFilter(),
            ])
            ->recordActions([
                ...ActionComponents::recordActions([
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
            ->sortable()
            ->alignCenter()
            ->toggleable();
    }

    protected static function sourceCurrencyColumn(): TextColumn
    {
        return TextColumn::make('source_currency')
            ->label(__('sn-wallet::wallet.market_rates.market_rate_table.source_currency'))
            ->searchable()
            ->badge();
    }

    protected static function targetCurrencyColumn(): TextColumn
    {
        return TextColumn::make('target_currency')
            ->label(__('sn-wallet::wallet.market_rates.market_rate_table.target_currency'))
            ->searchable()
            ->badge();
    }

    protected static function rateColumn(): TextColumn
    {
        return TextColumn::make('rate')
            ->label(__('sn-wallet::wallet.market_rates.market_rate_table.rate'))
            ->formatStateUsing(fn ($record) => $record->format())
            ->copyable();
    }

    protected static function createdAtColumn(): TextColumn
    {
        return TextColumn::make('created_at')
            ->label(__('sn-wallet::wallet.market_rates.market_rate_table.created_at'))
            ->sortable()
            ->toggleable();
    }

    protected static function updatedAtColumn(): TextColumn
    {
        return TextColumn::make('updated_at')
            ->label(__('sn-wallet::wallet.market_rates.market_rate_table.updated_at'))
            ->sortable()
            ->toggleable()
            ->toggledHiddenByDefault();
    }
}

<?php

namespace Wsmallnews\Wallet\Filament\Resources\Recharges\Tables;

use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Wsmallnews\Support\Filament\Actions\ActionComponents;
use Wsmallnews\Support\Filament\Filters\FilterComponents;
use Wsmallnews\Support\Filament\Tables\ColumnComponents;
use Wsmallnews\Wallet\Enums\RechargeStatus;
use Wsmallnews\Wallet\Support\Utils;

class RechargesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                static::idColumn(),
                static::ownerColumn(),
                static::walletTypeColumn(),
                static::walletAmountColumn(),
                static::payFeeColumn(),
                static::statusColumn(),
                static::paidAtColumn(),
                static::createdAtColumn(),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with(['owner']))
            ->defaultSort('id', 'desc')
            ->searchPlaceholder(__('sn-wallet::wallet.recharges.recharge_table.search_placeholder'))
            ->filtersFormWidth(Width::Medium)
            ->filters([
                static::statusFilter(),
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
            __('sn-wallet::wallet.recharges.recharge_table.owner'),
            fn ($record) => $record->owner,
            fn ($record) => $record->owner_type,
            fn ($record) => $record->owner_id,
        );
    }

    protected static function walletTypeColumn(): TextColumn
    {
        return TextColumn::make('wallet_type')
            ->label(__('sn-wallet::wallet.recharges.recharge_table.wallet_type'))
            ->badge()
            ->toggleable();
    }

    protected static function walletAmountColumn(): TextColumn
    {
        return TextColumn::make('wallet_amount')
            ->label(__('sn-wallet::wallet.recharges.recharge_table.wallet_amount'))
            ->formatStateUsing(fn ($record) => Utils::getWalletTypeModel()::query()->where('code', $record->wallet_type)->first()?->format((int) $record->wallet_amount) ?? $record->wallet_amount)
            ->alignCenter();
    }

    protected static function payFeeColumn(): TextColumn
    {
        return TextColumn::make('pay_fee')
            ->label(__('sn-wallet::wallet.recharges.recharge_table.pay_fee'))
            ->formatStateUsing(fn ($record) => sn_money()->decimal($record->pay_fee, $record->getPayCurrency()) . ' ' . $record->getPayCurrency())
            ->alignCenter();
    }

    protected static function statusColumn(): TextColumn
    {
        return TextColumn::make('status')
            ->label(__('sn-wallet::wallet.recharges.recharge_table.status'))
            ->badge();
    }

    protected static function paidAtColumn(): TextColumn
    {
        return TextColumn::make('paid_at')
            ->label(__('sn-wallet::wallet.recharges.recharge_table.paid_at'))
            ->toggleable();
    }

    protected static function createdAtColumn(): TextColumn
    {
        return TextColumn::make('created_at')
            ->label(__('sn-wallet::wallet.recharges.recharge_table.created_at'))
            ->sortable()
            ->toggleable();
    }

    // ========================= Filters =========================

    protected static function statusFilter(): SelectFilter
    {
        return FilterComponents::statusFilter(RechargeStatus::class)
            ->multiple();
    }
}

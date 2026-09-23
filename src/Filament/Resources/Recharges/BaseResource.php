<?php

namespace Wsmallnews\Wallet\Filament\Resources\Recharges;

use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use Wsmallnews\Wallet\Filament\Resources\Recharges\Tables\RechargesTable;
use Wsmallnews\Wallet\Support\Utils;

abstract class BaseResource extends Resource
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::Banknotes;

    protected static ?string $slug = 'wallet-recharges';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?int $navigationSort = 4;

    public static function getModel(): string
    {
        return Utils::getRechargeModel();
    }

    public static function getModelLabel(): string
    {
        return static::$modelLabel ?? __('sn-wallet::wallet.recharges.recharge_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return static::$pluralModelLabel ?? __('sn-wallet::wallet.recharges.recharge_resource.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? __('sn-wallet::wallet.recharges.recharge_resource.navigation_label');
    }

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return static::$navigationGroup ?? __('sn-wallet::wallet.global_default.navigation_group');
    }

    public static function table(Table $table): Table
    {
        return RechargesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('owner_type')
                    ->label(__('sn-wallet::wallet.recharges.recharge_table.owner'))
                    ->formatStateUsing(fn ($record) => $record->owner?->getSnName() ?? "{$record->owner_type}#{$record->owner_id}"),
                TextEntry::make('wallet_type')
                    ->label(__('sn-wallet::wallet.recharges.recharge_table.wallet_type'))
                    ->badge(),
                TextEntry::make('wallet_amount')
                    ->label(__('sn-wallet::wallet.recharges.recharge_table.wallet_amount'))
                    ->formatStateUsing(fn ($record) => Utils::getWalletTypeModel()::query()->where('code', $record->wallet_type)->first()?->format((int) $record->wallet_amount) ?? $record->wallet_amount),
                TextEntry::make('pay_fee')
                    ->label(__('sn-wallet::wallet.recharges.recharge_table.pay_fee'))
                    ->formatStateUsing(fn ($record) => sn_money()->decimal($record->pay_fee, $record->getPayCurrency()) . ' ' . $record->getPayCurrency()),
                TextEntry::make('status')
                    ->label(__('sn-wallet::wallet.recharges.recharge_table.status'))
                    ->badge(),
                TextEntry::make('paid_at')
                    ->label(__('sn-wallet::wallet.recharges.recharge_table.paid_at')),
                TextEntry::make('created_at')
                    ->label(__('sn-wallet::wallet.recharges.recharge_table.created_at')),
            ])->columns(3),
        ]);
    }
}

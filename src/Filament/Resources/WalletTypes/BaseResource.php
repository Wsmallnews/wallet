<?php

namespace Wsmallnews\Wallet\Filament\Resources\WalletTypes;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use Wsmallnews\Wallet\Filament\Resources\WalletTypes\RelationManagers\RatesRelationManager;
use Wsmallnews\Wallet\Filament\Resources\WalletTypes\Schemas\WalletTypeForm;
use Wsmallnews\Wallet\Filament\Resources\WalletTypes\Tables\WalletTypesTable;
use Wsmallnews\Wallet\Support\Utils;

abstract class BaseResource extends Resource
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::CreditCard;

    protected static ?string $slug = 'wallet-types';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    public static function getModel(): string
    {
        return Utils::getWalletTypeModel();
    }

    public static function getModelLabel(): string
    {
        return static::$modelLabel ?? __('sn-wallet::wallet.wallet_types.wallet_type_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return static::$pluralModelLabel ?? __('sn-wallet::wallet.wallet_types.wallet_type_resource.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? __('sn-wallet::wallet.wallet_types.wallet_type_resource.navigation_label');
    }

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return static::$navigationGroup ?? __('sn-wallet::wallet.global_default.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return WalletTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WalletTypesTable::configure($table);
    }

    /**
     * @return array<class-string>
     */
    public static function getRelations(): array
    {
        return [
            RatesRelationManager::class,
        ];
    }
}

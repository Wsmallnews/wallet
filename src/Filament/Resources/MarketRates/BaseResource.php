<?php

namespace Wsmallnews\Wallet\Filament\Resources\MarketRates;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use Wsmallnews\Wallet\Filament\Resources\MarketRates\Schemas\MarketRateForm;
use Wsmallnews\Wallet\Filament\Resources\MarketRates\Tables\MarketRatesTable;
use Wsmallnews\Wallet\Support\Utils;

abstract class BaseResource extends Resource
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::ArrowsRightLeft;

    protected static ?string $slug = 'wallet-market-rates';

    protected static ?int $navigationSort = 3;

    public static function getModel(): string
    {
        return Utils::getMarketRateModel();
    }

    public static function getModelLabel(): string
    {
        return static::$modelLabel ?? __('sn-wallet::wallet.market_rates.market_rate_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return static::$pluralModelLabel ?? __('sn-wallet::wallet.market_rates.market_rate_resource.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? __('sn-wallet::wallet.market_rates.market_rate_resource.navigation_label');
    }

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return static::$navigationGroup ?? __('sn-wallet::wallet.global_default.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return MarketRateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarketRatesTable::configure($table);
    }
}

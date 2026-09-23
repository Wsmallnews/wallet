<?php

namespace Wsmallnews\Wallet\Filament\Resources\MarketRates;

use Closure;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Wsmallnews\Support\Filament\Concerns\CanBeConfigured;
use Wsmallnews\Support\Filament\Resources\ResourceConfiguration;
use Wsmallnews\Wallet\Filament\Resources\MarketRates\Pages\CreateMarketRate;
use Wsmallnews\Wallet\Filament\Resources\MarketRates\Pages\EditMarketRate;
use Wsmallnews\Wallet\Filament\Resources\MarketRates\Pages\ListMarketRates;

final class MarketRateResource extends BaseResource
{
    use CanBeConfigured;

    protected static ?string $configurationClass = ResourceConfiguration::class;

    public static function getPages(): array
    {
        return [
            'index' => ListMarketRates::route('/'),
            'create' => CreateMarketRate::route('/create'),
            'edit' => EditMarketRate::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        $resolveForm = self::resolveCustomProperty('form');

        return $resolveForm instanceof Closure ? $resolveForm($schema, self::class) : parent::form($schema);
    }

    public static function table(Table $table): Table
    {
        $resolveTable = self::resolveCustomProperty('table');

        return $resolveTable instanceof Closure ? $resolveTable($table, self::class) : parent::table($table);
    }
}

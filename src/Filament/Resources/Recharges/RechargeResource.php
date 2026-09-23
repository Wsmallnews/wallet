<?php

namespace Wsmallnews\Wallet\Filament\Resources\Recharges;

use Closure;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Wsmallnews\Support\Filament\Concerns\CanBeConfigured;
use Wsmallnews\Support\Filament\Resources\ResourceConfiguration;
use Wsmallnews\Wallet\Filament\Resources\Recharges\Pages\ListRecharges;
use Wsmallnews\Wallet\Filament\Resources\Recharges\Pages\ViewRecharge;

final class RechargeResource extends BaseResource
{
    use CanBeConfigured;

    protected static ?string $configurationClass = ResourceConfiguration::class;

    public static function getPages(): array
    {
        return [
            'index' => ListRecharges::route('/'),
            'view' => ViewRecharge::route('/{record}'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        $resolveForm = self::resolveCustomProperty('form');

        return $resolveForm instanceof Closure ? $resolveForm($schema, self::class) : $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        $resolveTable = self::resolveCustomProperty('table');

        return $resolveTable instanceof Closure ? $resolveTable($table, self::class) : parent::table($table);
    }
}

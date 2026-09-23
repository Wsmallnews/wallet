<?php

namespace Wsmallnews\Wallet\Filament\Resources\WalletTypes;

use Closure;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Wsmallnews\Support\Filament\Concerns\CanBeConfigured;
use Wsmallnews\Support\Filament\Resources\ResourceConfiguration;
use Wsmallnews\Wallet\Filament\Resources\WalletTypes\Pages\CreateWalletType;
use Wsmallnews\Wallet\Filament\Resources\WalletTypes\Pages\EditWalletType;
use Wsmallnews\Wallet\Filament\Resources\WalletTypes\Pages\ListWalletTypes;
use Wsmallnews\Wallet\Filament\Resources\WalletTypes\Pages\ViewWalletType;

final class WalletTypeResource extends BaseResource
{
    use CanBeConfigured;

    protected static ?string $configurationClass = ResourceConfiguration::class;

    public static function getPages(): array
    {
        return [
            'index' => ListWalletTypes::route('/'),
            'create' => CreateWalletType::route('/create'),
            'view' => ViewWalletType::route('/{record}'),
            'edit' => EditWalletType::route('/{record}/edit'),
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

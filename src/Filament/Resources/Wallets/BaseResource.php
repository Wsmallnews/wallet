<?php

namespace Wsmallnews\Wallet\Filament\Resources\Wallets;

use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use Wsmallnews\Wallet\Filament\Resources\Wallets\RelationManagers\TransactionsRelationManager;
use Wsmallnews\Wallet\Filament\Resources\Wallets\Tables\WalletsTable;
use Wsmallnews\Wallet\Support\Utils;

abstract class BaseResource extends Resource
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedWallet;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::Wallet;

    protected static ?string $slug = 'wallets';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?int $navigationSort = 1;

    public static function getModel(): string
    {
        return Utils::getWalletModel();
    }

    public static function getModelLabel(): string
    {
        return static::$modelLabel ?? __('sn-wallet::wallet.wallets.wallet_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return static::$pluralModelLabel ?? __('sn-wallet::wallet.wallets.wallet_resource.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? __('sn-wallet::wallet.wallets.wallet_resource.navigation_label');
    }

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return static::$navigationGroup ?? __('sn-wallet::wallet.global_default.navigation_group');
    }

    public static function table(Table $table): Table
    {
        return WalletsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('owner_type')
                    ->label(__('sn-wallet::wallet.wallets.wallet_table.owner'))
                    ->formatStateUsing(fn ($record) => $record->owner?->getSnName() ?? "{$record->owner_type}#{$record->owner_id}"),
                TextEntry::make('walletType.name')
                    ->label(__('sn-wallet::wallet.wallets.wallet_table.wallet_type'))
                    ->formatStateUsing(fn ($record) => "{$record->walletType?->name}（{$record->walletType?->code}）"),
                TextEntry::make('balance')
                    ->label(__('sn-wallet::wallet.wallets.wallet_table.balance'))
                    ->formatStateUsing(fn ($record) => $record->formatBalance()),
                TextEntry::make('frozen')
                    ->label(__('sn-wallet::wallet.wallets.wallet_table.frozen'))
                    ->formatStateUsing(fn ($record) => $record->formatFrozen()),
                TextEntry::make('created_at')
                    ->label(__('sn-wallet::wallet.wallets.wallet_table.created_at')),
            ])->columns(3),
        ]);
    }

    /**
     * @return array<class-string>
     */
    public static function getRelations(): array
    {
        return [
            TransactionsRelationManager::class,
        ];
    }
}

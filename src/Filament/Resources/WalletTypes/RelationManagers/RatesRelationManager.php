<?php

namespace Wsmallnews\Wallet\Filament\Resources\WalletTypes\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * 锚定率管理：全局默认行（team_id 空）+ 租户覆盖行。
 * 类型存在性归代码声明；此处调整率参数（改动即时生效，在途支付按快照不受影响）。
 */
class RatesRelationManager extends RelationManager
{
    protected static string $relationship = 'rates';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('anchor_rate')
            ->heading(__('sn-wallet::wallet.wallet_types.rates_relation.title'))
            ->defaultSort('id', 'asc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->alignCenter(),
                TextColumn::make('team_id')
                    ->label(__('sn-wallet::wallet.wallet_types.rates_relation.team_id'))
                    ->formatStateUsing(fn ($record) => $record->team_id ?? __('—'))
                    ->badge()
                    ->color(fn ($record) => $record->isGlobal() ? 'gray' : 'info'),
                TextColumn::make('anchor_rate')
                    ->label(__('sn-wallet::wallet.wallet_types.rates_relation.anchor_rate'))
                    ->formatStateUsing(fn ($record) => $record->formatAnchor())
                    ->alignCenter(),
                IconColumn::make('enabled')
                    ->label(__('sn-wallet::wallet.wallet_types.rates_relation.enabled'))
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('updated_at')
                    ->label(__('sn-wallet::wallet.wallet_types.wallet_type_table.updated_at')),
            ])
            ->form([
                TextInput::make('team_id')
                    ->label(__('sn-wallet::wallet.wallet_types.rates_relation.team_id'))
                    ->helperText('留空 = 全局默认锚定率')
                    ->numeric()
                    ->nullable(),
                TextInput::make('anchor_rate')
                    ->label(__('sn-wallet::wallet.wallet_types.rates_relation.anchor_rate'))
                    ->required()
                    ->rules(['regex:/^\d{1,12}(\.\d{0,8})?$/']),
                TextInput::make('anchor_currency')
                    ->label(__('sn-wallet::wallet.wallet_types.rates_relation.anchor_currency'))
                    ->required()
                    ->maxLength(3)
                    ->rules(['alpha']),
                Toggle::make('enabled')
                    ->label(__('sn-wallet::wallet.wallet_types.rates_relation.enabled'))
                    ->default(true),
            ]);
    }
}

<?php

namespace Wsmallnews\Wallet\Filament\Resources\WalletTypes\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;

class WalletTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema(static::baseFields())->columns(2)->columnSpanFull(),
        ]);
    }

    /**
     * @return array<int, mixed>
     */
    public static function baseFields(): array
    {
        return [
            static::codeField(),
            static::nameField(),
            static::currencyCodeField(),
            static::decimalsField(),
            static::enabledField(),
            Group::make()->schema([
                static::anchorRateField(),
                static::anchorCurrencyField(),
            ])
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn (?string $operation): bool => $operation === 'create'),
        ];
    }

    public static function codeField(): TextInput
    {
        return TextInput::make('code')
            ->label(__('sn-wallet::wallet.wallet_types.wallet_type_table.code'))
            ->helperText('balance / point / energy')
            ->required()
            ->maxLength(20)
            ->rules(['alpha_dash'])
            ->unique(ignoreRecord: true)
            ->disabledOn('edit');
    }

    public static function nameField(): TextInput
    {
        return TextInput::make('name')
            ->label(__('sn-wallet::wallet.wallet_types.wallet_type_table.name'))
            ->required()
            ->maxLength(50);
    }

    public static function currencyCodeField(): TextInput
    {
        return TextInput::make('currency_code')
            ->label(__('sn-wallet::wallet.wallet_types.wallet_type_table.currency_code'))
            ->helperText('CNY / POINT / ENERGY')
            ->required()
            ->maxLength(10)
            ->default('CNY')
            ->rules(['alpha']);
    }

    public static function decimalsField(): TextInput
    {
        return TextInput::make('decimals')
            ->label(__('sn-wallet::wallet.wallet_types.wallet_type_table.decimals'))
            ->helperText('0 = 整数积分')
            ->numeric()
            ->minValue(0)
            ->maxValue(6)
            ->default(2)
            ->required();
    }

    public static function enabledField(): Toggle
    {
        return Toggle::make('enabled')
            ->label(__('sn-wallet::wallet.wallet_types.wallet_type_table.enabled'))
            ->default(true)
            ->inline(false);
    }

    public static function anchorRateField(): TextInput
    {
        return TextInput::make('anchor_rate')
            ->label(__('sn-wallet::wallet.wallet_types.rates_relation.anchor_rate'))
            ->helperText('1 锚定币种主单位 = ? 钱包主单位，如 100 积分 / 1 CNY 记 100')
            ->numeric()
            ->minValue(0)
            ->rules(['regex:/^\d{1,12}(\.\d{0,8})?$/']);
    }

    public static function anchorCurrencyField(): TextInput
    {
        return TextInput::make('anchor_currency')
            ->label(__('sn-wallet::wallet.wallet_types.rates_relation.anchor_currency'))
            ->helperText('ISO 4217，默认记账本位币')
            ->maxLength(3)
            ->rules(['alpha']);
    }
}

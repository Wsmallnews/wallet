<?php

namespace Wsmallnews\Wallet\Filament\Resources\MarketRates\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Wsmallnews\Wallet\Services\ConversionService;

class MarketRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema(static::baseFields())->columns(3)->columnSpanFull(),
        ]);
    }

    /**
     * @return array<int, mixed>
     */
    public static function baseFields(): array
    {
        return [
            static::sourceCurrencyField(),
            static::targetCurrencyField(),
            static::rateField(),
        ];
    }

    public static function sourceCurrencyField(): TextInput
    {
        return TextInput::make('source_currency')
            ->label(__('sn-wallet::wallet.market_rates.market_rate_table.source_currency'))
            ->helperText('ISO 4217；同一币种对以数据库唯一键约束')
            ->required()
            ->maxLength(3)
            ->rules(['alpha']);
    }

    public static function targetCurrencyField(): TextInput
    {
        return TextInput::make('target_currency')
            ->label(__('sn-wallet::wallet.market_rates.market_rate_table.target_currency'))
            ->helperText(fn (): string => '通常为记账本位币（' . app(ConversionService::class)->baseCurrency() . '）')
            ->required()
            ->maxLength(3)
            ->rules(['alpha']);
    }

    public static function rateField(): TextInput
    {
        return TextInput::make('rate')
            ->label(__('sn-wallet::wallet.market_rates.market_rate_table.rate'))
            ->helperText('1 源币种 = ? 目标币种，如 1 USD = 7.2 CNY 记 7.2')
            ->required()
            ->rules(['regex:/^\d{1,10}(\.\d{0,10})?$/']);
    }
}

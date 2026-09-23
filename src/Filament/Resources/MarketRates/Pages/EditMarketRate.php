<?php

namespace Wsmallnews\Wallet\Filament\Resources\MarketRates\Pages;

use Filament\Resources\Pages\EditRecord;
use Wsmallnews\Wallet\Filament\Resources\MarketRates\MarketRateResource;

class EditMarketRate extends EditRecord
{
    protected static string $resource = MarketRateResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['source_currency'] = strtoupper((string) ($data['source_currency'] ?? ''));
        $data['target_currency'] = strtoupper((string) ($data['target_currency'] ?? ''));

        return $data;
    }
}

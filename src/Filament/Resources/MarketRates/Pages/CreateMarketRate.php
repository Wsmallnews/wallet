<?php

namespace Wsmallnews\Wallet\Filament\Resources\MarketRates\Pages;

use Filament\Resources\Pages\CreateRecord;
use Wsmallnews\Wallet\Filament\Resources\MarketRates\MarketRateResource;

class CreateMarketRate extends CreateRecord
{
    protected static string $resource = MarketRateResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['source_currency'] = strtoupper((string) ($data['source_currency'] ?? ''));
        $data['target_currency'] = strtoupper((string) ($data['target_currency'] ?? ''));

        return $data;
    }
}

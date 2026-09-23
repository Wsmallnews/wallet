<?php

namespace Wsmallnews\Wallet\Filament\Resources\WalletTypes\Pages;

use Filament\Resources\Pages\EditRecord;
use Wsmallnews\Wallet\Filament\Resources\WalletTypes\WalletTypeResource;

class EditWalletType extends EditRecord
{
    protected static string $resource = WalletTypeResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['currency_code'] = strtoupper((string) ($data['currency_code'] ?? 'CNY'));

        return $data;
    }
}

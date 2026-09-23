<?php

namespace Wsmallnews\Wallet\Filament\Resources\WalletTypes\Pages;

use Filament\Resources\Pages\CreateRecord;
use Wsmallnews\Wallet\Filament\Resources\WalletTypes\WalletTypeResource;
use Wsmallnews\Wallet\Services\ConversionService;
use Wsmallnews\Wallet\Support\Utils;

class CreateWalletType extends CreateRecord
{
    protected static string $resource = WalletTypeResource::class;

    /**
     * 初始锚定率参数（非类型表字段，afterCreate 落 rate 行）
     *
     * @var array<string, mixed>
     */
    protected array $anchorPayload = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->anchorPayload = [
            'anchor_rate' => $data['anchor_rate'] ?? null,
            'anchor_currency' => isset($data['anchor_currency']) && $data['anchor_currency'] !== '' ? strtoupper($data['anchor_currency']) : null,
        ];

        unset($data['anchor_rate'], $data['anchor_currency']);

        $data['code'] = strtolower((string) $data['code']);
        $data['currency_code'] = strtoupper((string) ($data['currency_code'] ?? 'CNY'));

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->anchorPayload['anchor_rate'] !== null && $this->anchorPayload['anchor_rate'] !== '') {
            Utils::getWalletRateModel()::create([
                'wallet_type_id' => $this->getRecord()->id,
                'team_id' => null,
                'anchor_rate' => (string) $this->anchorPayload['anchor_rate'],
                'anchor_currency' => $this->anchorPayload['anchor_currency'] ?? app(ConversionService::class)->baseCurrency(),
                'enabled' => true,
            ]);
        }
    }
}

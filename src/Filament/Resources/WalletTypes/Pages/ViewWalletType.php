<?php

namespace Wsmallnews\Wallet\Filament\Resources\WalletTypes\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Wsmallnews\Wallet\Filament\Resources\WalletTypes\WalletTypeResource;

class ViewWalletType extends ViewRecord
{
    protected static string $resource = WalletTypeResource::class;

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

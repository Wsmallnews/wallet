<?php

namespace Wsmallnews\Wallet\Filament\Resources\Wallets\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Wsmallnews\Wallet\Filament\Resources\Wallets\Actions\AdjustActions;
use Wsmallnews\Wallet\Filament\Resources\Wallets\WalletResource;

class ViewWallet extends ViewRecord
{
    protected static string $resource = WalletResource::class;

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return AdjustActions::actions();
    }
}

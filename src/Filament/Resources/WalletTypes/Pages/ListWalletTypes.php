<?php

namespace Wsmallnews\Wallet\Filament\Resources\WalletTypes\Pages;

use Filament\Resources\Pages\ListRecords;
use Wsmallnews\Wallet\Filament\Resources\WalletTypes\WalletTypeResource;

class ListWalletTypes extends ListRecords
{
    protected static string $resource = WalletTypeResource::class;
}

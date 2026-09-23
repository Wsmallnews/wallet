<?php

namespace Wsmallnews\Wallet\Commands;

use Wsmallnews\Support\Commands\PackageInstallCommand;

class WalletInstallCommand extends PackageInstallCommand
{
    protected string $packageName = 'sn-wallet';
}

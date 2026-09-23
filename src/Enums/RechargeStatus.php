<?php

namespace Wsmallnews\Wallet\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

enum RechargeStatus: string implements HasColor, HasIcon, HasLabel
{
    use EnumHelper;

    case Unpaid = 'unpaid';

    case Paid = 'paid';

    case Closed = 'closed';

    public function getLabel(): ?string
    {
        return __('sn-wallet::wallet.recharge_status.' . $this->value);
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Unpaid => 'warning',
            self::Paid => 'success',
            self::Closed => 'gray',
        };
    }

    public function getIcon(): string | \BackedEnum | null
    {
        return match ($this) {
            self::Unpaid => Heroicon::OutlinedClock,
            self::Paid => Heroicon::OutlinedCheckCircle,
            self::Closed => Heroicon::OutlinedNoSymbol,
        };
    }
}

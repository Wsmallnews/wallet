<?php

namespace Wsmallnews\Wallet\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

enum TransactionType: string implements HasColor, HasIcon, HasLabel
{
    use EnumHelper;

    case Recharge = 'recharge';

    case Consume = 'consume';

    case Refund = 'refund';

    case Adjust = 'adjust';

    case Freeze = 'freeze';

    case Unfreeze = 'unfreeze';

    case FreezeConsume = 'freeze_consume';

    case FrozenCredit = 'frozen_credit';

    case TransferIn = 'transfer_in';

    case TransferOut = 'transfer_out';

    public function getLabel(): ?string
    {
        return __('sn-wallet::wallet.transaction_types.' . $this->value);
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Recharge, self::TransferIn => 'success',
            self::Refund, self::Unfreeze => 'primary',
            self::Consume, self::TransferOut => 'info',
            self::Freeze, self::FrozenCredit => 'warning',
            self::FreezeConsume => 'danger',
            self::Adjust => 'gray',
        };
    }

    public function getIcon(): string | \BackedEnum | null
    {
        return match ($this) {
            self::Recharge => Heroicon::OutlinedArrowDownCircle,
            self::Consume => Heroicon::OutlinedArrowUpCircle,
            self::Refund => Heroicon::OutlinedArrowUturnLeft,
            self::Adjust => Heroicon::OutlinedAdjustmentsHorizontal,
            self::Freeze, self::FrozenCredit => Heroicon::OutlinedLockClosed,
            self::Unfreeze => Heroicon::OutlinedLockOpen,
            self::FreezeConsume => Heroicon::OutlinedMinusCircle,
            self::TransferIn => Heroicon::OutlinedArrowLeftCircle,
            self::TransferOut => Heroicon::OutlinedArrowRightCircle,
        };
    }
}

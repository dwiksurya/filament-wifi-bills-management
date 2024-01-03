<?php

namespace App\Enums;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasLabel, HasColor
{
    case PAID = 'paid';
    case NOT_PAID_YET = 'not paid yet';
    case CANCELED = 'canceled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PAID => 'Paid',
            self::NOT_PAID_YET => 'Not Paid Yet',
            self::CANCELED => 'Canceled',

        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PAID => 'success',
            self::NOT_PAID_YET => 'secondary',
            self::CANCELED => 'danger',
        };
    }
}

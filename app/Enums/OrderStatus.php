<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case Done = 'Done'; 

    case Not_yet = 'Not yet';
 
    case Canceled = 'Canceled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Done => 'Done',
            self::Not_yet => 'Not yet',
            self::Canceled => 'Canceled',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Not_yet => 'warning',
            self::Done => 'success',
            self::Canceled => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Not_yet => 'heroicon-m-arrow-path',
            self::Done => 'heroicon-m-check-badge',
            self::Canceled => 'heroicon-m-x-circle',
        };
    }
}

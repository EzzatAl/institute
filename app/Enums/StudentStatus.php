<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum StudentStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'Active'; 

    case Pending = 'Pending';

    case InActive = 'InActive';
 
    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Pending => 'Pending',
            self::InActive => 'InActive',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Active => 'success',
            self::Pending => 'warning',
            self::InActive => 'danger',

        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Active => 'heroicon-m-check-badge',
            self::Pending => 'heroicon-m-arrow-path',
            self::InActive => 'heroicon-m-x-circle',
        };
    }
}

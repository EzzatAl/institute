<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RegisterCourseStatus: string implements HasColor, HasIcon, HasLabel
{
    case Enrolled = 'Enrolled'; 

    case Not_yet = 'Not yet';

    public function getLabel(): string
    {
        return match ($this) {
            self::Enrolled => 'Enrolled',
            self::Not_yet => 'Not yet',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Enrolled => 'success',
            self::Not_yet => 'warning',

        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Enrolled => 'heroicon-m-check-badge',
            self::Not_yet => 'heroicon-m-arrow-path',
        };
    }
}

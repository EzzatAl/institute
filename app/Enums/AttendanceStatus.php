<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AttendanceStatus: string implements HasColor, HasIcon, HasLabel
{
    case Attended = 'Attended'; 

    case Absent = 'Absent';

    case Late = 'Late';
 
    public function getLabel(): string
    {
        return match ($this) {
            self::Attended => 'Attended',
            self::Late => 'Late',
            self::Absent => 'Absent',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Attended => 'success',
            self::Late => 'warning',
            self::Absent => 'danger',

        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Attended => 'heroicon-m-check-badge',
            self::Late => 'heroicon-m-x-circle',
            self::Absent => 'heroicon-m-arrow-path',
        };
    }
}

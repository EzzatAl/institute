<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CourseStatus: string implements HasColor, HasIcon, HasLabel
{
    case To_Open = 'To Open'; 

    case Open = 'Open'; 
 
    case Finished = 'Finished';

    public function getLabel(): string
    {
        return match ($this) {
            self::To_Open => 'To Open',
            self::Open => 'Open',
            self::Finished => 'Finished',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Open => 'success',
            self::To_Open => 'warning',
            self::Finished => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Open => 'heroicon-m-check-badge',
            self::To_Open => 'heroicon-m-arrow-path',
            self::Finished => 'heroicon-m-x-circle',
        };
    }
}

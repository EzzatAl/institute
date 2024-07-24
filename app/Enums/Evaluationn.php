<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Evaluationn: string implements HasColor, HasIcon, HasLabel
{
    case Excellent = 'Excellent'; 
    
    case Very_Good = 'Very Good'; 

    case Good = 'Good';

    case Fair = 'Fair';
 
    public function getLabel(): string
    {
        return match ($this) {
            self::Excellent => 'Excellent',
            self::Very_Good => 'Very Good',
            self::Good => 'Good',
            self::Fair => 'Fair',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Excellent => 'success',
            self::Very_Good => 'warning',
            self::Good => 'warning',
            self::Fair => 'danger',

        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Excellent => 'heroicon-m-check-badge',
            self::Very_Good => 'heroicon-m-x-circle',
            self::Good => 'heroicon-m-x-circle',
            self::Fair => 'heroicon-m-arrow-path',
        };
    }
}

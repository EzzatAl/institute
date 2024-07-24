<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CourseType: string implements HasColor, HasIcon, HasLabel
{
    case Intensive = 'Intensive';

    case Regular = 'Regular';

    case Private = 'Private';

    public function getLabel(): string
    {
        return match ($this) {
            self::Intensive => 'Intensive',
            self::Regular => 'Regular',
            self::Private => 'Private',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Regular => 'info',
            self::Intensive => 'warning',
            self::Private => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Regular => '',
            self::Intensive => 'heroicon-s-fire',
            self::Private => 'heroicon-s-lock-closed',
        };
    }
}

<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Where a demo stands in the label's review, set in the admin.
 */
enum DemoStatus: string implements HasColor, HasLabel
{
    case New = 'new';
    case Listened = 'listened';
    case Accepted = 'accepted';
    case Declined = 'declined';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Listened => 'Listened',
            self::Accepted => 'Accepted',
            self::Declined => 'Declined',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'info',
            self::Listened => 'warning',
            self::Accepted => 'success',
            self::Declined => 'gray',
        };
    }
}

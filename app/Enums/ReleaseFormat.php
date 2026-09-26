<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ReleaseFormat: string implements HasColor, HasLabel
{
    case Single = 'single';
    case Ep = 'ep';
    case Album = 'album';
    case Compilation = 'compilation';

    public function getLabel(): string
    {
        return match ($this) {
            self::Single => 'Single',
            self::Ep => 'EP',
            self::Album => 'Album',
            self::Compilation => 'Compilation',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Single => 'gray',
            self::Ep => 'info',
            self::Album => 'success',
            self::Compilation => 'warning',
        };
    }
}

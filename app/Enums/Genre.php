<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Genre: string implements HasLabel
{
    case TechHouse = 'tech-house';
    case DeepHouse = 'deep-house';
    case House = 'house';
    case Techno = 'techno';
    case MelodicTechno = 'melodic-techno';
    case AfroHouse = 'afro-house';

    public function getLabel(): string
    {
        return match ($this) {
            self::TechHouse => 'Tech House',
            self::DeepHouse => 'Deep House',
            self::House => 'House',
            self::Techno => 'Techno',
            self::MelodicTechno => 'Melodic Techno',
            self::AfroHouse => 'Afro House',
        };
    }
}

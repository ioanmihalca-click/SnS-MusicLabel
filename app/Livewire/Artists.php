<?php

namespace App\Livewire;

use App\Models\Artist;
use Illuminate\Support\Str;
use Livewire\Component;

class Artists extends Component
{
    private const DESCRIPTION_PREVIEW_LENGTH = 220;

    public function render()
    {
        $artists = Artist::query()
            ->orderBy('order')
            ->get()
            ->each(function (Artist $artist): void {
                $plain = trim(strip_tags((string) $artist->description));
                $artist->setAttribute('plain_description', $plain);
                $artist->setAttribute(
                    'short_description',
                    Str::limit($plain, self::DESCRIPTION_PREVIEW_LENGTH)
                );
                $artist->setAttribute(
                    'is_truncated',
                    mb_strlen($plain) > self::DESCRIPTION_PREVIEW_LENGTH
                );
            });

        return view('livewire.artists', compact('artists'));
    }
}

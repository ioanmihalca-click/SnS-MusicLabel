<?php

namespace App\Livewire;

use App\Models\Release;
use Illuminate\Support\Str;
use Livewire\Component;

class Releases extends Component
{
    /**
     * How many characters to show in the description preview before "Read More".
     */
    private const DESCRIPTION_PREVIEW_LENGTH = 220;

    /**
     * Maximum releases rendered on the homepage. Bound to keep the page light.
     */
    private const RELEASES_LIMIT = 24;

    public function render()
    {
        $releases = Release::query()
            ->latest()
            ->limit(self::RELEASES_LIMIT)
            ->get()
            ->each(function (Release $release): void {
                $plain = trim(strip_tags((string) $release->description));
                $release->setAttribute('plain_description', $plain);
                $release->setAttribute(
                    'short_description',
                    Str::limit($plain, self::DESCRIPTION_PREVIEW_LENGTH)
                );
                $release->setAttribute(
                    'is_truncated',
                    mb_strlen($plain) > self::DESCRIPTION_PREVIEW_LENGTH
                );
            });

        return view('livewire.releases', [
            'releases' => $releases,
        ]);
    }
}

<?php

namespace App\Livewire;

use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Release;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class StatsStrip extends Component
{
    /**
     * How long to cache aggregate counts. Stats change rarely (admin-driven),
     * so an hour is a reasonable default — flushed automatically at TTL,
     * and tests run with the array store which never persists across runs.
     */
    private const CACHE_TTL_SECONDS = 3600;

    public function render()
    {
        $stats = Cache::remember('homepage.stats-strip', self::CACHE_TTL_SECONDS, fn () => [
            ['value' => Artist::count(), 'label' => 'Artists'],
            ['value' => Release::count(), 'label' => 'Releases'],
            ['value' => Playlist::where('is_active', true)->count(), 'label' => 'Playlists'],
            ['value' => 4, 'label' => 'Genres'],
            ['value' => 2020, 'label' => 'Established', 'noPad' => true],
        ]);

        return view('livewire.stats-strip', [
            'stats' => $stats,
        ]);
    }
}

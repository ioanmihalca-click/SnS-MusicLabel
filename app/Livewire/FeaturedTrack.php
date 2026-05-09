<?php

namespace App\Livewire;

use App\Models\FeaturedTrack as FeaturedTrackModel;
use Livewire\Component;

class FeaturedTrack extends Component
{
    public function render()
    {
        $track = FeaturedTrackModel::query()
            ->active()
            ->ordered()
            ->first();

        return view('livewire.featured-track', [
            'track' => $track,
        ]);
    }
}

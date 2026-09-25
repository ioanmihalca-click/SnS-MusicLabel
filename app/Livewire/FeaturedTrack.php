<?php

namespace App\Livewire;

use App\Models\Release;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class FeaturedTrack extends Component
{
    public function render(): View
    {
        $release = Release::featured()->with('artists')->first();

        return view('livewire.featured-track', [
            'release' => $release,
            'coverUrl' => $release?->coverUrl(),
        ]);
    }
}

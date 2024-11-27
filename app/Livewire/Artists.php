<?php

namespace App\Livewire;

use App\Models\Artist;
use Livewire\Component;

class Artists extends Component
{
    public function render()
    {
        $artists = Artist::orderBy('order')->get();
        return view('livewire.artists', compact('artists'));
    }
}
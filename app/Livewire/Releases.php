<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\Release;

class Releases extends Component
{
    public function render()
    {
        $releases = Release::orderBy('created_at', 'desc')->get();

        return view('livewire.releases', [
            'releases' => $releases,
        ]);
    }
}
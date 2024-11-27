<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Playlist;

class PlaylistSlider extends Component
{
    public $currentIndex = 0;
    public $playlists;

    public function mount()
    {
        $this->playlists = Playlist::where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    public function nextSlide()
    {
        if($this->currentIndex < count($this->playlists) - 1) {
            $this->currentIndex++;
        } else {
            $this->currentIndex = 0;
        }
    }

    public function previousSlide()
    {
        if($this->currentIndex > 0) {
            $this->currentIndex--;
        } else {
            $this->currentIndex = count($this->playlists) - 1;
        }
    }

    public function goToSlide($index)
    {
        $this->currentIndex = $index;
    }

    public function render()
    {
        return view('livewire.playlist-slider');
    }
}
<?php

namespace App\Livewire;

use Livewire\Component;

class Hero extends Component
{
    public $showTitle = false;
    public $showBrand = false;
    public $showSubtitle = false;
    public $showDescription = false;
    public $showButtons = false;

    public function mount()
    {
        $this->dispatch('startAnimations');
    }

    public function render()
    {
        return view('livewire.hero');
    }
}
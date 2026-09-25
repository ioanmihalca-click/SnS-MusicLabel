<?php

namespace App\Livewire;

use App\Models\Photo;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The photo gallery on /about: newest first, 12 per page, opened in the
 * Fancybox lightbox (bound in resources/js/app.js).
 */
class PhotoGallery extends Component
{
    use WithPagination;

    public $perPage = 12;

    public function render(): View
    {
        return view('livewire.photo-gallery', [
            'photos' => Photo::latest()->paginate($this->perPage),
        ]);
    }
}

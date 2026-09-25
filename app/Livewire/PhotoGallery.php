<?php

namespace App\Livewire;

use App\Models\Photo;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class PhotoGallery extends Component
{
    use WithPagination;

    public const VARIANT_LEGACY = 'legacy';

    public const VARIANT_SITE = 'site';

    public $perPage = 12;

    /**
     * The look: the old homepage section, or the redesigned grid used on /about.
     */
    #[Locked]
    public string $variant = self::VARIANT_LEGACY;

    public function render(): View
    {
        return view($this->variant === self::VARIANT_SITE ? 'livewire.photo-gallery-site' : 'livewire.photo-gallery', [
            'photos' => Photo::latest()->paginate($this->perPage),
        ]);
    }
}

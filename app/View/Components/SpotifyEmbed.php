<?php

namespace App\View\Components;

use App\Support\Spotify\SpotifyUrl;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * A bare Spotify player `<iframe>` (no wrapper element, so parents can style
 * it with `[&>iframe]:...`). Renders nothing when the URL is not a Spotify link.
 */
class SpotifyEmbed extends Component
{
    public ?SpotifyUrl $spotifyUrl;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public ?string $url = null,
        public int|string $height = 352,
    ) {
        $this->spotifyUrl = SpotifyUrl::parse($url);
    }

    public function shouldRender(): bool
    {
        return $this->spotifyUrl !== null;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.spotify-embed');
    }
}

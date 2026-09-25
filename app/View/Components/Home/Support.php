<?php

namespace App\View\Components\Home;

use App\Models\Release;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\Component;

/**
 * The homepage's "Supported & played by" band, generated from the releases'
 * DJ support and chart positions. Hidden while there is neither.
 */
class Support extends Component
{
    public const MAX_SUPPORTERS = 16;

    public const MAX_CHARTS = 4;

    /**
     * The DJs who supported the releases, newest releases first, each named once.
     *
     * @var list<string>
     */
    public array $supporters;

    /**
     * The releases that entered a chart, newest first.
     *
     * @var Collection<int, Release>
     */
    public Collection $charts;

    /**
     * @param  Collection<int, Release>  $releases  The catalogue, newest first.
     */
    public function __construct(Collection $releases)
    {
        $this->supporters = $releases
            ->flatMap(fn (Release $release): array => $release->support ?? [])
            ->map(fn (mixed $name): string => Str::squish((string) $name))
            ->filter()
            ->unique(fn (string $name): string => mb_strtolower($name))
            ->take(self::MAX_SUPPORTERS)
            ->values()
            ->all();

        $this->charts = $releases
            ->filter(fn (Release $release): bool => filled($release->chart_position))
            ->take(self::MAX_CHARTS)
            ->values();
    }

    public function shouldRender(): bool
    {
        return $this->supporters !== [] || $this->charts->isNotEmpty();
    }

    public function render(): View|Closure|string
    {
        return view('components.home.support');
    }
}

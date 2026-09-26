<?php

namespace App\Livewire;

use App\Enums\Genre;
use App\Enums\ReleaseFormat;
use App\Models\Artist;
use App\Models\Release;
use App\Support\Seo\Schema;
use App\Support\Seo\SeoData;
use App\Support\Spotify\SpotifyThumbnail;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * The full catalogue at /releases, filterable by artist, format, genre and year.
 *
 * The filters are plain strings from the query string: values that are not a
 * roster artist, a format, a genre or a year are ignored rather than rejected,
 * and every filtered view keeps /releases as its canonical URL. The catalogue
 * is small (a few dozen releases), so it is loaded once, without pagination,
 * and the filter options are counted from it.
 */
#[Layout('components.layouts.site')]
class ReleaseCatalogue extends Component
{
    public const DESCRIPTION = "Every release on the Snow 'n' Stuff label, newest first: Tech House, Deep House, House, Techno and Melodic Techno singles and compilations.";

    /**
     * @var list<string>
     */
    private const FILTERS = ['artist', 'format', 'genre', 'year'];

    /**
     * An artist slug, e.g. "g-and-s".
     *
     * The four filters hold strings but are declared untyped: a malformed query
     * string (?artist[]=x) must be ignored, not fail on assignment. See normalizeFilters().
     *
     * @var string
     */
    #[Url(except: '')]
    public $artist = '';

    /**
     * A ReleaseFormat value, e.g. "single".
     *
     * @var string
     */
    #[Url(except: '')]
    public $format = '';

    /**
     * A Genre value, e.g. "melodic-techno".
     *
     * @var string
     */
    #[Url(except: '')]
    public $genre = '';

    /**
     * A release year, e.g. "2025".
     *
     * @var string
     */
    #[Url(except: '')]
    public $year = '';

    public function mount(): void
    {
        $this->normalizeFilters();
    }

    public function updated(): void
    {
        $this->normalizeFilters();
    }

    public function clearFilters(): void
    {
        $this->reset(...self::FILTERS);
    }

    public function render(SpotifyThumbnail $thumbnails): View
    {
        $catalogue = Release::query()->with('artists')->newestFirst()->get();
        $artists = $catalogue->pluck('artists')->flatten(1)->unique('id')->sortBy([['order', 'asc'], ['name', 'asc']])->values();

        $selectedArtist = $artists->firstWhere('slug', $this->artist);
        $selectedFormat = ReleaseFormat::tryFrom($this->format);
        $selectedGenre = Genre::tryFrom($this->genre);
        $selectedYear = ctype_digit($this->year) ? (int) $this->year : null;

        $releases = $catalogue
            ->filter(fn (Release $release): bool => ($selectedArtist === null || $release->artists->contains($selectedArtist))
                && ($selectedFormat === null || $release->format === $selectedFormat)
                && ($selectedGenre === null || $release->genre === $selectedGenre)
                && ($selectedYear === null || $release->released_at?->year === $selectedYear))
            ->values();

        $thumbnails->warmArtwork($releases);

        return view('livewire.release-catalogue', [
            'releases' => $releases,
            'isFiltered' => $selectedArtist !== null || $selectedFormat !== null || $selectedGenre !== null || $selectedYear !== null,
            'filters' => $this->filterOptions($catalogue, $artists),
            'trail' => $this->trail(),
        ])->layoutData([
            'seo' => $this->seo(),
        ]);
    }

    /**
     * The four selects: each option is labelled with its number of releases,
     * and only values present in the catalogue are offered.
     *
     * @param  Collection<int, Release>  $catalogue
     * @param  BaseCollection<int, Artist>  $artists
     * @return array<string, array{label: string, all: string, options: array<string, string>}>
     */
    private function filterOptions(Collection $catalogue, BaseCollection $artists): array
    {
        $count = fn (callable $matches): int => $catalogue->filter($matches)->count();

        return [
            'artist' => [
                'label' => 'Artist',
                'all' => 'All artists',
                'options' => $artists->mapWithKeys(fn (Artist $artist): array => [
                    $artist->slug => "{$artist->name} (".$count(fn (Release $release): bool => $release->artists->contains($artist)).')',
                ])->all(),
            ],
            'format' => [
                'label' => 'Format',
                'all' => 'All formats',
                'options' => collect(ReleaseFormat::cases())->mapWithKeys(fn (ReleaseFormat $format): array => [
                    $format->value => $count(fn (Release $release): bool => $release->format === $format),
                ])->filter()->map(fn (int $total, string $value): string => ReleaseFormat::from($value)->getLabel()." ({$total})")->all(),
            ],
            'genre' => [
                'label' => 'Genre',
                'all' => 'All genres',
                'options' => collect(Genre::cases())->mapWithKeys(fn (Genre $genre): array => [
                    $genre->value => $count(fn (Release $release): bool => $release->genre === $genre),
                ])->filter()->map(fn (int $total, string $value): string => Genre::from($value)->getLabel()." ({$total})")->all(),
            ],
            'year' => [
                'label' => 'Year',
                'all' => 'All years',
                'options' => $catalogue->pluck('released_at')->filter()
                    ->countBy(fn (CarbonInterface $releasedAt): int => $releasedAt->year)
                    ->sortKeysDesc()
                    ->map(fn (int $total, int $year): string => "{$year} ({$total})")
                    ->all(),
            ],
        ];
    }

    /**
     * Keep every filter a string: numbers decoded from the query string
     * (?year=2025 arrives as an integer) become strings again, anything else
     * (arrays, booleans...) is dropped.
     */
    private function normalizeFilters(): void
    {
        foreach (self::FILTERS as $filter) {
            $value = $this->{$filter};

            $this->{$filter} = is_string($value) || is_int($value) ? (string) $value : '';
        }
    }

    /**
     * @return array<string, string>
     */
    private function trail(): array
    {
        return ['/' => 'Home', route('releases.index', absolute: false) => 'Releases'];
    }

    /**
     * Filtered views are the same page: the canonical is always /releases.
     */
    private function seo(): SeoData
    {
        return new SeoData(
            title: 'Releases - '.SeoData::SITE_NAME,
            description: self::DESCRIPTION,
            path: route('releases.index', absolute: false),
            schema: [
                Schema::breadcrumbs($this->trail()),
            ],
        );
    }
}

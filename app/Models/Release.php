<?php

namespace App\Models;

use App\Enums\Genre;
use App\Enums\ReleaseFormat;
use App\Observers\PublicContentObserver;
use App\Support\PlainText;
use App\Support\Seo\SeoData;
use App\Support\Slug;
use App\Support\Spotify\HasSpotifyArtwork;
use App\Support\Spotify\SpotifyUrl;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(PublicContentObserver::class)]
class Release extends Model
{
    use HasFactory;
    use HasSpotifyArtwork;

    /**
     * What separates the names in a typed credit: commas, or "&", "x", "feat.", "vs." between spaces
     * (so "G&S" stays one name).
     */
    private const CREDIT_SEPARATOR_PATTERN = '/(\s*,\s*|\s+(?:&|x|feat\.?|ft\.?|vs\.?)\s+)/iu';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'released_at',
        'format',
        'genre',
        'artist_display',
        'spotify_url',
        'smartlink_url',
        'cover_image',
        'is_featured',
        'support',
        'chart_position',
        'chart_name',
    ];

    protected function casts(): array
    {
        return [
            'released_at' => 'date',
            'format' => ReleaseFormat::class,
            'genre' => Genre::class,
            'is_featured' => 'boolean',
            'support' => 'array',
        ];
    }

    /**
     * Generate the slug only while it is empty, so editing the title keeps the public URL.
     */
    protected static function booted(): void
    {
        static::saving(function (Release $release): void {
            if (filled($release->slug)) {
                return;
            }

            $release->slug = Slug::unique(
                (string) $release->title,
                fn (string $slug): bool => static::query()
                    ->where('slug', $slug)
                    ->when($release->exists, fn (Builder $query) => $query->whereKeyNot($release->getKey()))
                    ->exists(),
            );
        });
    }

    /**
     * Roster artists credited on the release, in roster order. Guest artists only
     * appear in `artist_display`.
     */
    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class)
            ->orderBy('artists.order')
            ->orderBy('artists.name');
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class)->orderBy('position');
    }

    /**
     * Releases promoted in the homepage hero, newest first.
     */
    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true)
            ->orderByDesc('released_at')
            ->orderByDesc('id');
    }

    /**
     * Newest first; legacy releases without a date come last.
     */
    #[Scope]
    protected function newestFirst(Builder $query): void
    {
        $query->orderByRaw($query->qualifyColumn('released_at').' is null')
            ->orderByDesc($query->qualifyColumn('released_at'))
            ->orderByDesc($query->qualifyColumn('id'));
    }

    /**
     * The public credit: the full `artist_display` text when set (e.g. "THK & Pacha Man"),
     * otherwise the names of the linked roster artists.
     */
    protected function credit(): Attribute
    {
        return Attribute::get(fn (): string => filled($this->artist_display)
            ? $this->artist_display
            : $this->artists->pluck('name')->join(', ', ' & ')
        );
    }

    /**
     * The credit split into names and separators, each name paired with its
     * roster artist when one is linked, so the page can link the roster names:
     * "THK & Pacha Man" gives THK (linked), " & " and "Pacha Man".
     *
     * @return list<array{text: string, artist: Artist|null}>
     */
    public function creditParts(): array
    {
        if (blank($this->artist_display)) {
            $artists = $this->artists->values();

            return $artists->flatMap(fn (Artist $artist, int $index): array => [
                ...($index === 0 ? [] : [['text' => $index === $artists->count() - 1 ? ' & ' : ', ', 'artist' => null]]),
                ['text' => $artist->name, 'artist' => $artist],
            ])->all();
        }

        $artistsByName = $this->artists->keyBy(fn (Artist $artist): string => mb_strtolower($artist->name));
        $parts = preg_split(self::CREDIT_SEPARATOR_PATTERN, $this->artist_display, flags: PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        return array_map(fn (string $text): array => [
            'text' => $text,
            'artist' => $artistsByName->get(mb_strtolower(trim($text))),
        ], $parts ?: [$this->artist_display]);
    }

    /**
     * Keep only the canonical open.spotify.com URL, whatever shape was pasted.
     */
    protected function spotifyUrl(): Attribute
    {
        return Attribute::set(fn (?string $value): ?string => blank($value)
            ? null
            : (SpotifyUrl::parse($value)?->url() ?? trim($value))
        );
    }

    /**
     * The uploaded artwork, or Spotify's own thumbnail until one is uploaded.
     */
    public function coverUrl(): ?string
    {
        return $this->artworkUrl();
    }

    protected function uploadedArtworkPath(): ?string
    {
        return $this->cover_image;
    }

    /**
     * Where "Listen now" leads: the distributor's smartlink (every platform,
     * pre-save before release day) or else Spotify.
     */
    public function listenUrl(): ?string
    {
        return $this->smartlink_url ?: $this->spotify_url;
    }

    /**
     * The start of the description, or a one-line factual summary when there is none.
     */
    public function summary(): string
    {
        $excerpt = PlainText::excerpt($this->description);

        if ($excerpt !== '') {
            return $excerpt;
        }

        $summary = ($this->format?->getLabel() ?? 'Release')
            .(filled($this->credit) ? " by {$this->credit}" : '')
            .' on '.SeoData::SITE_NAME;

        if ($this->released_at !== null) {
            $summary .= ', released '.$this->released_at->format('j F Y');
        }

        return "{$this->title}: {$summary}.";
    }
}

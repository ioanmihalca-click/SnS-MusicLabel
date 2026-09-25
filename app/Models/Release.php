<?php

namespace App\Models;

use App\Enums\Genre;
use App\Enums\ReleaseFormat;
use App\Support\Slug;
use App\Support\Spotify\SpotifyThumbnail;
use App\Support\Spotify\SpotifyUrl;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Release extends Model
{
    use HasFactory;

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
        if (filled($this->cover_image)) {
            return asset('storage/'.$this->cover_image);
        }

        $spotifyUrl = SpotifyUrl::parse($this->spotify_url);

        return $spotifyUrl === null ? null : app(SpotifyThumbnail::class)->urlFor($spotifyUrl);
    }
}

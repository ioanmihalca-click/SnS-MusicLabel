<?php

namespace App\Models;

use App\Observers\PublicContentObserver;
use App\Support\PlainText;
use App\Support\Seo\SeoData;
use App\Support\Slug;
use App\Support\Spotify\HasSpotifyArtwork;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ObservedBy(PublicContentObserver::class)]
class Artist extends Model
{
    use HasFactory;
    use HasSpotifyArtwork;

    protected $fillable = [
        'name',
        'slug',
        'order',
        'spotify_url',
        'description',
        'photo',
        'role',
        'origin',
        'instagram_url',
        'soundcloud_url',
        'beatport_url',
        'highlights',
        'press_kit',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'highlights' => 'array',
        ];
    }

    /**
     * Generate the slug only while it is empty, so renaming the artist keeps the public URL.
     */
    protected static function booted(): void
    {
        static::saving(function (Artist $artist): void {
            if (filled($artist->slug)) {
                return;
            }

            $artist->slug = Slug::unique(
                (string) $artist->name,
                fn (string $slug): bool => static::query()
                    ->where('slug', $slug)
                    ->when($artist->exists, fn (Builder $query) => $query->whereKeyNot($artist->getKey()))
                    ->exists(),
            );
        });
    }

    public function releases(): BelongsToMany
    {
        return $this->belongsToMany(Release::class);
    }

    /**
     * The roster order used everywhere on the site.
     */
    #[Scope]
    protected function inRosterOrder(Builder $query): void
    {
        $query->orderBy('order')->orderBy('name');
    }

    /**
     * The uploaded portrait, or the Spotify profile picture until one is uploaded.
     */
    public function photoUrl(): ?string
    {
        return $this->artworkUrl();
    }

    protected function uploadedArtworkPath(): ?string
    {
        return $this->photo;
    }

    public function pressKitUrl(): ?string
    {
        return filled($this->press_kit) ? asset('storage/'.$this->press_kit) : null;
    }

    /**
     * The artist's own profiles, keyed by platform name, e.g. ['Spotify' => 'https://open.spotify.com/artist/...'].
     *
     * @return array<string, string>
     */
    public function profileUrls(): array
    {
        return array_filter([
            'Spotify' => $this->spotify_url,
            'Instagram' => $this->instagram_url,
            'SoundCloud' => $this->soundcloud_url,
            'Beatport' => $this->beatport_url,
        ], fn (?string $url): bool => filled($url));
    }

    /**
     * The start of the bio, or a one-line summary when there is none.
     */
    public function summary(): string
    {
        $excerpt = PlainText::excerpt($this->description);

        if ($excerpt !== '') {
            return $excerpt;
        }

        return filled($this->role)
            ? "{$this->name}, {$this->role} on ".SeoData::SITE_NAME.'.'
            : "{$this->name} on ".SeoData::SITE_NAME.'.';
    }
}

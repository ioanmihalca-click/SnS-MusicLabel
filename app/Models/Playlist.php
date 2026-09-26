<?php

namespace App\Models;

use App\Observers\PublicContentObserver;
use App\Support\Spotify\HasSpotifyArtwork;
use App\Support\Spotify\SpotifyUrl;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(PublicContentObserver::class)]
class Playlist extends Model
{
    use HasFactory;
    use HasSpotifyArtwork;

    public const FALLBACK_TITLE = 'Spotify playlist';

    protected $fillable = [
        'title',
        'description',
        'spotify_url',
        'cover_image',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_active' => 'boolean',
        ];
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
     * Playlists shown on the site, in the admin's order.
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('order');
    }

    /**
     * The title, or a generic one for rows converted from the old site without a title.
     */
    public function displayTitle(): string
    {
        return filled($this->title) ? $this->title : self::FALLBACK_TITLE;
    }

    /**
     * The `spotify:` URI the footer player loads, or null when the Spotify link
     * is missing, a short link or nothing the embed can play.
     */
    public function playUri(): ?string
    {
        $spotifyUrl = SpotifyUrl::parse($this->spotify_url);

        return $spotifyUrl?->isPlayable() ? $spotifyUrl->uri() : null;
    }

    /**
     * The uploaded cover, or Spotify's own playlist image until one is uploaded.
     */
    public function coverUrl(): ?string
    {
        return $this->artworkUrl();
    }

    protected function uploadedArtworkPath(): ?string
    {
        return $this->cover_image;
    }
}

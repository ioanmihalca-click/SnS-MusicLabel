<?php

namespace App\Models;

use Database\Factories\FeaturedTrackFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeaturedTrack extends Model
{
    /** @use HasFactory<FeaturedTrackFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'artist_name',
        'spotify_track_url',
        'cover_image',
        'released_at',
        'order',
        'is_active',
    ];

    /**
     * Mirror the migration defaults so unsaved instances behave consistently.
     */
    protected $attributes = [
        'order' => 0,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'released_at' => 'date',
            'order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Only return featured tracks marked as active.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Apply the canonical hero ordering: lowest `order` first, then most recently released.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderByDesc('released_at');
    }

    /**
     * Best-effort extraction of the 22-char Spotify track ID from the canonical URL.
     */
    protected function spotifyTrackId(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->spotify_track_url) {
                return null;
            }

            preg_match('#/track/([A-Za-z0-9]+)#', $this->spotify_track_url, $matches);

            return $matches[1] ?? null;
        });
    }

    /**
     * Embed src derived from the track ID, ready for any future inline-player option.
     */
    protected function spotifyEmbedSrc(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->spotify_track_id
            ? "https://open.spotify.com/embed/track/{$this->spotify_track_id}"
            : null
        );
    }
}

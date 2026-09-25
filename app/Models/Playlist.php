<?php

namespace App\Models;

use App\Support\Spotify\SpotifyUrl;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

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
}

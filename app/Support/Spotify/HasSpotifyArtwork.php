<?php

namespace App\Support\Spotify;

/**
 * Artwork for a model with a `spotify_url`: the image uploaded in the admin,
 * or Spotify's own 300px thumbnail until one is uploaded.
 */
trait HasSpotifyArtwork
{
    /**
     * The uploaded image's path on the public disk, if any.
     */
    abstract protected function uploadedArtworkPath(): ?string;

    public function hasUploadedArtwork(): bool
    {
        return filled($this->uploadedArtworkPath());
    }

    /**
     * The Spotify link whose thumbnail stands in for the artwork: null once an
     * image is uploaded, or without a usable Spotify link. Pages look these up
     * all at once before rendering, with SpotifyThumbnail::warmArtwork().
     */
    public function artworkSpotifyUrl(): ?SpotifyUrl
    {
        return $this->hasUploadedArtwork() ? null : SpotifyUrl::parse($this->spotify_url);
    }

    /**
     * The uploaded image, or the Spotify thumbnail (looked up and cached on first use).
     */
    public function artworkUrl(): ?string
    {
        if ($this->hasUploadedArtwork()) {
            return asset('storage/'.$this->uploadedArtworkPath());
        }

        $spotifyUrl = $this->artworkSpotifyUrl();

        return $spotifyUrl === null ? null : app(SpotifyThumbnail::class)->urlFor($spotifyUrl);
    }

    /**
     * Like artworkUrl(), but never calls Spotify: a thumbnail counts only if
     * it is already cached. Used by sitemap.xml.
     */
    public function cachedArtworkUrl(): ?string
    {
        if ($this->hasUploadedArtwork()) {
            return asset('storage/'.$this->uploadedArtworkPath());
        }

        $spotifyUrl = $this->artworkSpotifyUrl();

        return $spotifyUrl === null ? null : app(SpotifyThumbnail::class)->cachedUrlFor($spotifyUrl);
    }
}

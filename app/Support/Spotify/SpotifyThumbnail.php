<?php

namespace App\Support\Spotify;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Looks up Spotify's own cover thumbnail (300px) through the public oEmbed
 * endpoint. Only the URL is cached, and only temporarily: 30 days after a
 * successful lookup (catalogue pages list many releases, so they should not
 * call Spotify on every visit), 1h after a failed one. The cached value is
 * wrapped in an array so that failures (null) are remembered too.
 */
class SpotifyThumbnail
{
    public const ENDPOINT = 'https://open.spotify.com/oembed';

    private const SUCCESS_TTL_SECONDS = 30 * 86400;

    private const FAILURE_TTL_SECONDS = 3600;

    public function urlFor(SpotifyUrl $spotifyUrl): ?string
    {
        $cached = Cache::get($this->cacheKey($spotifyUrl));

        if (is_array($cached) && array_key_exists('url', $cached)) {
            return $cached['url'];
        }

        $thumbnailUrl = $this->fetch($spotifyUrl);

        Cache::put(
            $this->cacheKey($spotifyUrl),
            ['url' => $thumbnailUrl],
            $thumbnailUrl === null ? self::FAILURE_TTL_SECONDS : self::SUCCESS_TTL_SECONDS,
        );

        return $thumbnailUrl;
    }

    /**
     * The thumbnail only if an earlier lookup already cached it: never calls
     * Spotify, so generated documents such as sitemap.xml stay fast.
     */
    public function cachedUrlFor(SpotifyUrl $spotifyUrl): ?string
    {
        $cached = Cache::get($this->cacheKey($spotifyUrl));

        return is_array($cached) && is_string($cached['url'] ?? null) ? $cached['url'] : null;
    }

    private function cacheKey(SpotifyUrl $spotifyUrl): string
    {
        return "spotify.thumbnail.{$spotifyUrl->type}.{$spotifyUrl->id}";
    }

    private function fetch(SpotifyUrl $spotifyUrl): ?string
    {
        try {
            $response = Http::connectTimeout(2)
                ->timeout(3)
                ->acceptJson()
                ->get(self::ENDPOINT, ['url' => $spotifyUrl->url()]);
        } catch (ConnectionException) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $thumbnailUrl = $response->json('thumbnail_url');

        return is_string($thumbnailUrl) && Str::startsWith($thumbnailUrl, 'https://') ? $thumbnailUrl : null;
    }
}

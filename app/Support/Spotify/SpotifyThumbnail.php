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
        $cacheKey = "spotify.thumbnail.{$spotifyUrl->type}.{$spotifyUrl->id}";

        $cached = Cache::get($cacheKey);

        if (is_array($cached) && array_key_exists('url', $cached)) {
            return $cached['url'];
        }

        $thumbnailUrl = $this->fetch($spotifyUrl);

        Cache::put(
            $cacheKey,
            ['url' => $thumbnailUrl],
            $thumbnailUrl === null ? self::FAILURE_TTL_SECONDS : self::SUCCESS_TTL_SECONDS,
        );

        return $thumbnailUrl;
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

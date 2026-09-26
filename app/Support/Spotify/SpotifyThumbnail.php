<?php

namespace App\Support\Spotify;

use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Release;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Looks up Spotify's own cover thumbnail (300px) through the public oEmbed
 * endpoint. Only the URL is cached, and only temporarily: 30 to 35 days after
 * a successful lookup (catalogue pages list many releases, so they should not
 * call Spotify on every visit; the extra days are random, so the thumbnails
 * cached together do not all expire on the same day). The cached value is
 * wrapped in an array so that failures (null) are remembered too.
 *
 * Each successful lookup also keeps the URL as the "last good" thumbnail for
 * 180 days. Spotify's terms allow cover art and metadata to be cached only
 * temporarily and require keeping them up to date: the 30-day refresh keeps
 * the URL current, and 180 days after the last successful lookup is the hard
 * limit (a fallback never extends it or outlives it). When a lookup fails (an
 * error, a timeout, an unreachable Spotify or anything but an https URL), the
 * last good thumbnail is cached again for a day, so the cover stays on the
 * page and Spotify is asked again tomorrow, not on every visit. Without one,
 * the failure is remembered for 10 minutes only, so a brief outage during a
 * first lookup hides the cover only briefly.
 *
 * Pages that show many covers call warm() before rendering: the thumbnails
 * not cached yet are then looked up all at once, in parallel, and urlFor()
 * only reads the cache.
 */
class SpotifyThumbnail
{
    public const ENDPOINT = 'https://open.spotify.com/oembed';

    private const SUCCESS_TTL_SECONDS = 30 * 86400;

    private const SUCCESS_TTL_JITTER_SECONDS = 5 * 86400;

    private const LAST_GOOD_TTL_SECONDS = 180 * 86400;

    private const LAST_GOOD_FALLBACK_TTL_SECONDS = 86400;

    private const FAILURE_TTL_SECONDS = 600;

    public function urlFor(SpotifyUrl $spotifyUrl): ?string
    {
        $cached = Cache::get($this->cacheKey($spotifyUrl));

        if ($this->isLookup($cached)) {
            return $cached['url'];
        }

        try {
            $response = $this->lookUp(Http::createPendingRequest(), $spotifyUrl);
        } catch (ConnectionException $exception) {
            $response = $exception;
        }

        return $this->remember($spotifyUrl, $response);
    }

    /**
     * Look up, in a single round of parallel requests, every thumbnail that
     * is not cached yet (a failure counts as cached until it expires).
     *
     * @param  iterable<SpotifyUrl>  $spotifyUrls
     */
    public function warm(iterable $spotifyUrls): void
    {
        $uncached = $this->uncached($spotifyUrls);

        if ($uncached === []) {
            return;
        }

        $responses = Http::pool(function (Pool $pool) use ($uncached): void {
            foreach ($uncached as $cacheKey => $spotifyUrl) {
                $this->lookUp($pool->as($cacheKey), $spotifyUrl);
            }
        });

        foreach ($uncached as $cacheKey => $spotifyUrl) {
            $this->remember($spotifyUrl, $responses[$cacheKey]);
        }
    }

    /**
     * warm() for the artwork of models that show Spotify's thumbnail until an
     * image is uploaded (HasSpotifyArtwork). Models with an uploaded image or
     * without a Spotify link, and nulls (e.g. no hero release), are skipped.
     *
     * @param  iterable<Artist|Playlist|Release|null>  $models
     */
    public function warmArtwork(iterable $models): void
    {
        $spotifyUrls = [];

        foreach ($models as $model) {
            $spotifyUrl = $model?->artworkSpotifyUrl();

            if ($spotifyUrl !== null) {
                $spotifyUrls[] = $spotifyUrl;
            }
        }

        $this->warm($spotifyUrls);
    }

    /**
     * The thumbnail only if an earlier lookup already cached it, or else the
     * last good one: never calls Spotify, so generated documents such as
     * sitemap.xml stay fast.
     */
    public function cachedUrlFor(SpotifyUrl $spotifyUrl): ?string
    {
        $cached = Cache::get($this->cacheKey($spotifyUrl));

        if ($this->isLookup($cached)) {
            return is_string($cached['url']) ? $cached['url'] : null;
        }

        return $this->lastGood($spotifyUrl)['url'] ?? null;
    }

    private function cacheKey(SpotifyUrl $spotifyUrl): string
    {
        return "spotify.thumbnail.{$spotifyUrl->type}.{$spotifyUrl->id}";
    }

    private function lastGoodCacheKey(SpotifyUrl $spotifyUrl): string
    {
        return "spotify.thumbnail.last.{$spotifyUrl->type}.{$spotifyUrl->id}";
    }

    /**
     * The thumbnail of the last successful lookup, with the time (a Unix
     * timestamp) it may be shown until, if that is less than 180 days ago.
     *
     * @return array{url: string, expires_at: int}|null
     */
    private function lastGood(SpotifyUrl $spotifyUrl): ?array
    {
        $lastGood = Cache::get($this->lastGoodCacheKey($spotifyUrl));

        return is_array($lastGood) && is_string($lastGood['url'] ?? null) && is_int($lastGood['expires_at'] ?? null)
            ? $lastGood
            : null;
    }

    /**
     * Whether a cached value is the result of an earlier lookup, successful or not.
     *
     * @phpstan-assert-if-true array{url: string|null} $cached
     */
    private function isLookup(mixed $cached): bool
    {
        return is_array($cached) && array_key_exists('url', $cached);
    }

    /**
     * The URLs whose thumbnail is not cached, each once, keyed by cache key.
     *
     * @param  iterable<SpotifyUrl>  $spotifyUrls
     * @return array<string, SpotifyUrl>
     */
    private function uncached(iterable $spotifyUrls): array
    {
        $byCacheKey = [];

        foreach ($spotifyUrls as $spotifyUrl) {
            $byCacheKey[$this->cacheKey($spotifyUrl)] = $spotifyUrl;
        }

        if ($byCacheKey === []) {
            return [];
        }

        $cached = Cache::many(array_keys($byCacheKey));

        return array_filter(
            $byCacheKey,
            fn (string $cacheKey): bool => ! $this->isLookup($cached[$cacheKey] ?? null),
            ARRAY_FILTER_USE_KEY,
        );
    }

    /**
     * Send the oEmbed request: the response of a synchronous request, or the
     * promise of one added to a pool.
     */
    private function lookUp(PendingRequest $request, SpotifyUrl $spotifyUrl): Response|PromiseInterface
    {
        return $request->connectTimeout(2)
            ->timeout(3)
            ->acceptJson()
            ->get(self::ENDPOINT, ['url' => $spotifyUrl->url()]);
    }

    /**
     * Cache the thumbnail found in the answer, also as the last good one, and
     * return it. On a failure (an error, an unreachable Spotify or anything but
     * an https URL), cache and return the last good thumbnail for a day (never
     * past its 180 days) instead, or else remember the failure for 10 minutes
     * and return null.
     */
    private function remember(SpotifyUrl $spotifyUrl, Response|Throwable $response): ?string
    {
        $thumbnailUrl = $response instanceof Response && $response->successful()
            ? $response->json('thumbnail_url')
            : null;

        if (is_string($thumbnailUrl) && Str::startsWith($thumbnailUrl, 'https://')) {
            Cache::put(
                $this->cacheKey($spotifyUrl),
                ['url' => $thumbnailUrl],
                self::SUCCESS_TTL_SECONDS + random_int(0, self::SUCCESS_TTL_JITTER_SECONDS),
            );
            Cache::put(
                $this->lastGoodCacheKey($spotifyUrl),
                ['url' => $thumbnailUrl, 'expires_at' => now()->getTimestamp() + self::LAST_GOOD_TTL_SECONDS],
                self::LAST_GOOD_TTL_SECONDS,
            );

            return $thumbnailUrl;
        }

        $lastGood = $this->lastGood($spotifyUrl);

        if ($lastGood === null) {
            Cache::put($this->cacheKey($spotifyUrl), ['url' => null], self::FAILURE_TTL_SECONDS);

            return null;
        }

        Cache::put(
            $this->cacheKey($spotifyUrl),
            ['url' => $lastGood['url']],
            min(self::LAST_GOOD_FALLBACK_TTL_SECONDS, $lastGood['expires_at'] - now()->getTimestamp()),
        );

        return $lastGood['url'];
    }
}

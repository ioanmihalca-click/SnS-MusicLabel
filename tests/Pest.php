<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()
    ->extend(TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

/**
 * Decode the page's JSON-LD, asserting there is exactly one block.
 *
 * @return array{'@context': string, '@graph': list<array<string, mixed>>}
 */
function jsonLd(TestResponse $response): array
{
    preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $response->getContent(), $matches);

    expect($matches[1])->toHaveCount(1);

    return json_decode($matches[1][0], true, flags: JSON_THROW_ON_ERROR);
}

/**
 * Answer every Spotify oEmbed lookup with the given thumbnail.
 */
function fakeSpotifyThumbnails(string $thumbnailUrl = 'https://image-cdn-ak.spotifycdn.com/image/fake-thumbnail'): void
{
    Http::fake([
        'open.spotify.com/oembed*' => Http::response(['thumbnail_url' => $thumbnailUrl]),
    ]);
}

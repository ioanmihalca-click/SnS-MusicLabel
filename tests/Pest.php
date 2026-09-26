<?php

use App\Support\Spotify\SpotifyThumbnail;
use Dom\HTMLDocument;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Event;
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
 * The response's HTML as a document, to query with CSS selectors.
 */
function htmlDocument(TestResponse|string $html): HTMLDocument
{
    return HTMLDocument::createFromString($html instanceof TestResponse ? $html->getContent() : $html, LIBXML_NOERROR);
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

/**
 * Answer each Spotify oEmbed lookup with a thumbnail of its own (the Spotify
 * ID after image/) until an outage is set on the returned object, e.g.
 * `$spotify['outage'] = Http::failedConnection()`; from then on, every lookup
 * gets the outage's answer instead.
 *
 * @return ArrayObject<'outage', Closure|PromiseInterface|null>
 */
function fakeSpotifyThumbnailsUntilOutage(): ArrayObject
{
    $spotify = new ArrayObject(['outage' => null]);

    Http::fake([
        'open.spotify.com/oembed*' => fn (Request $request): Closure|PromiseInterface => $spotify['outage'] ?? Http::response([
            'thumbnail_url' => 'https://image-cdn-ak.spotifycdn.com/image/'.basename($request['url']),
        ]),
    ]);

    return $spotify;
}

/**
 * Record, in order, each Spotify oEmbed lookup as it is sent ('sent') and its
 * answer as it arrives ('received'). Compare with oneParallelRound().
 *
 * @return ArrayObject<int, 'sent'|'received'>
 */
function recordSpotifyLookups(): ArrayObject
{
    $lookups = new ArrayObject;
    $isLookup = fn (RequestSending|ResponseReceived $event): bool => str_starts_with($event->request->url(), SpotifyThumbnail::ENDPOINT);

    Event::listen(RequestSending::class, function (RequestSending $event) use ($lookups, $isLookup): void {
        if ($isLookup($event)) {
            $lookups[] = 'sent';
        }
    });
    Event::listen(ResponseReceived::class, function (ResponseReceived $event) use ($lookups, $isLookup): void {
        if ($isLookup($event)) {
            $lookups[] = 'received';
        }
    });

    return $lookups;
}

/**
 * The lookups recorded for a single round of parallel requests: every request
 * is sent before the first answer arrives.
 *
 * @return list<'sent'|'received'>
 */
function oneParallelRound(int $requests): array
{
    return [...array_fill(0, $requests, 'sent'), ...array_fill(0, $requests, 'received')];
}

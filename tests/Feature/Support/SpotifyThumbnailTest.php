<?php

use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Release;
use App\Support\Spotify\SpotifyThumbnail;
use App\Support\Spotify\SpotifyUrl;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('returns the oEmbed thumbnail of the canonical Spotify URL', function () {
    Http::fake([
        'open.spotify.com/oembed*' => Http::response(['thumbnail_url' => 'https://image-cdn-ak.spotifycdn.com/image/ab67616d00001e02']),
    ]);

    $thumbnailUrl = app(SpotifyThumbnail::class)->urlFor(SpotifyUrl::parse('https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr?si=abc'));

    expect($thumbnailUrl)->toBe('https://image-cdn-ak.spotifycdn.com/image/ab67616d00001e02');
    Http::assertSent(fn (Request $request): bool => $request['url'] === 'https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr');
});

it('caches a thumbnail for 30 to 35 days', function () {
    Http::fake([
        'open.spotify.com/oembed*' => Http::response(['thumbnail_url' => 'https://image-cdn-ak.spotifycdn.com/image/cover']),
    ]);
    $spotifyUrl = SpotifyUrl::parse('https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N');
    $thumbnail = app(SpotifyThumbnail::class);
    $this->travelTo('2026-01-01 00:00:00');

    $thumbnail->urlFor($spotifyUrl);
    $this->travelTo('2026-01-30 23:59:00');
    $thumbnail->urlFor($spotifyUrl);
    Http::assertSentCount(1);

    $this->travelTo('2026-02-05 00:01:00');
    $thumbnail->urlFor($spotifyUrl);
    Http::assertSentCount(2);
});

it('spreads the expiry of thumbnails cached together over five days', function () {
    fakeSpotifyThumbnails();
    $spotifyUrls = collect(range(1, 40))->map(fn (int $number): SpotifyUrl => SpotifyUrl::fromParts('album', str_pad((string) $number, 22, '0', STR_PAD_LEFT)));
    $thumbnail = app(SpotifyThumbnail::class);
    $this->travelTo('2026-01-01 00:00:00');

    $thumbnail->warm($spotifyUrls);
    $this->travelTo('2026-02-02 12:00:00');
    $thumbnail->warm($spotifyUrls);

    // Halfway through the five days: all 40 on the same side has a 2 in 10^12 chance.
    expect(Http::recorded()->count() - 40)
        ->toBeGreaterThan(0)
        ->toBeLessThan(40);
});

it('keeps the last good thumbnail for 180 days after a successful lookup, and no longer', function () {
    $spotify = fakeSpotifyThumbnailsUntilOutage();
    $spotifyUrl = SpotifyUrl::parse('https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N');
    $thumbnail = app(SpotifyThumbnail::class);
    $this->travelTo('2026-01-01 00:00:00');
    $thumbnail->urlFor($spotifyUrl);
    $spotify['outage'] = Http::failedConnection();

    $this->travelTo('2026-06-29 23:00:00');
    expect($thumbnail->urlFor($spotifyUrl))->toBe('https://image-cdn-ak.spotifycdn.com/image/3zifCl5R2DaZGEmrPNUM1N');

    // 180 days after the successful lookup: the fallback cached for a day an hour ago ends too.
    $this->travelTo('2026-06-30 00:00:01');
    expect($thumbnail->cachedUrlFor($spotifyUrl))->toBeNull()
        ->and($thumbnail->urlFor($spotifyUrl))->toBeNull();
});

it('uses the last good thumbnail for a day when a refresh fails', function (Closure $failure) {
    $spotify = fakeSpotifyThumbnailsUntilOutage();
    $lookups = recordSpotifyLookups();
    $spotifyUrl = SpotifyUrl::parse('https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N');
    $thumbnail = app(SpotifyThumbnail::class);
    $this->travelTo('2026-01-01 00:00:00');
    $thumbnail->urlFor($spotifyUrl);
    $spotify['outage'] = $failure;

    $this->travelTo('2026-02-10 00:00:00');
    expect($thumbnail->urlFor($spotifyUrl))->toBe('https://image-cdn-ak.spotifycdn.com/image/3zifCl5R2DaZGEmrPNUM1N');
    $this->travelTo('2026-02-10 23:59:00');
    expect($thumbnail->urlFor($spotifyUrl))->toBe('https://image-cdn-ak.spotifycdn.com/image/3zifCl5R2DaZGEmrPNUM1N')
        ->and(array_count_values($lookups->getArrayCopy())['sent'])->toBe(2);

    $this->travelTo('2026-02-11 00:01:00');
    expect($thumbnail->urlFor($spotifyUrl))->toBe('https://image-cdn-ak.spotifycdn.com/image/3zifCl5R2DaZGEmrPNUM1N')
        ->and(array_count_values($lookups->getArrayCopy())['sent'])->toBe(3);
})->with([
    'unreachable or timed out' => fn () => Http::failedConnection(),
    'an error' => fn () => Http::response(['error' => 'server error'], 500),
    'an invalid answer' => fn () => Http::response(['thumbnail_url' => 'javascript:alert(1)']),
]);

it('returns null and remembers the failure for 10 minutes when a first lookup fails', function () {
    Http::fake([
        'open.spotify.com/oembed*' => Http::response(['error' => 'not found'], 404),
    ]);
    $spotifyUrl = SpotifyUrl::parse('https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr');
    $thumbnail = app(SpotifyThumbnail::class);

    expect($thumbnail->urlFor($spotifyUrl))->toBeNull();
    $this->travel(9)->minutes();
    expect($thumbnail->urlFor($spotifyUrl))->toBeNull();
    Http::assertSentCount(1);

    $this->travel(2)->minutes();
    $thumbnail->urlFor($spotifyUrl);
    Http::assertSentCount(2);
});

it('returns null when Spotify cannot be reached', function () {
    Http::fake([
        'open.spotify.com/oembed*' => Http::failedConnection(),
    ]);

    expect(app(SpotifyThumbnail::class)->urlFor(SpotifyUrl::parse('https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr')))->toBeNull();
});

it('ignores a thumbnail that is not an https URL', function () {
    Http::fake([
        'open.spotify.com/oembed*' => Http::response(['thumbnail_url' => 'javascript:alert(1)']),
    ]);

    expect(app(SpotifyThumbnail::class)->urlFor(SpotifyUrl::parse('https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr')))->toBeNull();
});

it('reads a cached thumbnail without ever calling Spotify', function () {
    Http::fake([
        'open.spotify.com/oembed*' => Http::response(['thumbnail_url' => 'https://image-cdn-ak.spotifycdn.com/image/cover']),
    ]);
    $cached = SpotifyUrl::parse('https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N');
    $unknown = SpotifyUrl::parse('https://open.spotify.com/album/7kRBMHJQEsklIRTNH0qRfp');
    $thumbnail = app(SpotifyThumbnail::class);

    $thumbnail->urlFor($cached);

    expect($thumbnail->cachedUrlFor($cached))->toBe('https://image-cdn-ak.spotifycdn.com/image/cover')
        ->and($thumbnail->cachedUrlFor($unknown))->toBeNull();
    Http::assertSentCount(1);
});

it('reads the last good thumbnail once the cached one has expired, without calling Spotify', function () {
    fakeSpotifyThumbnails('https://image-cdn-ak.spotifycdn.com/image/cover');
    $spotifyUrl = SpotifyUrl::parse('https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N');
    $thumbnail = app(SpotifyThumbnail::class);
    $this->travelTo('2026-01-01 00:00:00');
    $thumbnail->urlFor($spotifyUrl);

    $this->travelTo('2026-03-01 00:00:00');

    expect($thumbnail->cachedUrlFor($spotifyUrl))->toBe('https://image-cdn-ak.spotifycdn.com/image/cover');
    Http::assertSentCount(1);
});

it('looks up every thumbnail in one round of parallel requests', function () {
    fakeSpotifyThumbnails('https://image-cdn-ak.spotifycdn.com/image/cover');
    $lookups = recordSpotifyLookups();
    $spotifyUrls = [
        SpotifyUrl::parse('https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N'),
        SpotifyUrl::parse('https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr'),
        SpotifyUrl::parse('https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO'),
    ];
    $thumbnail = app(SpotifyThumbnail::class);

    $thumbnail->warm($spotifyUrls);

    expect($lookups->getArrayCopy())->toBe(oneParallelRound(3))
        ->and(array_map($thumbnail->cachedUrlFor(...), $spotifyUrls))->toBe(array_fill(0, 3, 'https://image-cdn-ak.spotifycdn.com/image/cover'));
    Http::assertSent(fn (Request $request): bool => $request['url'] === 'https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO');
});

it('looks up only the thumbnails not cached yet, each once', function () {
    fakeSpotifyThumbnails();
    $cached = SpotifyUrl::parse('https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N');
    $uncached = SpotifyUrl::parse('https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr');
    $thumbnail = app(SpotifyThumbnail::class);
    $thumbnail->urlFor($cached);

    $thumbnail->warm([$cached, $uncached, SpotifyUrl::parse('https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr?si=abc')]);

    Http::assertSentCount(2);
    Http::assertSent(fn (Request $request): bool => $request['url'] === 'https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr');
});

it('sends nothing when every thumbnail is cached or there is none to warm', function () {
    fakeSpotifyThumbnails();
    $spotifyUrl = SpotifyUrl::parse('https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N');
    $thumbnail = app(SpotifyThumbnail::class);
    $thumbnail->urlFor($spotifyUrl);

    $thumbnail->warm([$spotifyUrl]);
    $thumbnail->warm([]);

    Http::assertSentCount(1);
});

it('remembers failed first lookups for 10 minutes', function () {
    Http::fake([
        'open.spotify.com/oembed*album*' => Http::failedConnection(),
        'open.spotify.com/oembed*track*' => Http::response(['error' => 'not found'], 404),
    ]);
    $lookups = recordSpotifyLookups();
    $unreachable = SpotifyUrl::parse('https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N');
    $notFound = SpotifyUrl::parse('https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr');
    $thumbnail = app(SpotifyThumbnail::class);

    $thumbnail->warm([$unreachable, $notFound]);
    $this->travel(9)->minutes();
    $thumbnail->warm([$unreachable, $notFound]);

    expect($thumbnail->urlFor($unreachable))->toBeNull()
        ->and($thumbnail->urlFor($notFound))->toBeNull()
        ->and(array_count_values($lookups->getArrayCopy())['sent'])->toBe(2);

    $this->travel(2)->minutes();
    $thumbnail->warm([$unreachable, $notFound]);

    expect(array_count_values($lookups->getArrayCopy())['sent'])->toBe(4);
});

it('uses the last good thumbnails when lookups in the parallel round fail', function () {
    $spotify = fakeSpotifyThumbnailsUntilOutage();
    $album = SpotifyUrl::parse('https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N');
    $track = SpotifyUrl::parse('https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr');
    $neverFound = SpotifyUrl::parse('https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO');
    $thumbnail = app(SpotifyThumbnail::class);
    $this->travelTo('2026-01-01 00:00:00');
    $thumbnail->warm([$album, $track]);
    $spotify['outage'] = Http::failedConnection();
    $lookups = recordSpotifyLookups();
    $sent = fn (): int => array_count_values($lookups->getArrayCopy())['sent'] ?? 0;

    $this->travelTo('2026-02-10 00:00:00');
    $thumbnail->warm([$album, $track, $neverFound]);

    expect($sent())->toBe(3)
        ->and($thumbnail->urlFor($album))->toBe('https://image-cdn-ak.spotifycdn.com/image/3zifCl5R2DaZGEmrPNUM1N')
        ->and($thumbnail->urlFor($track))->toBe('https://image-cdn-ak.spotifycdn.com/image/5LoRtT4HMphu4n2OyJn4Cr')
        ->and($thumbnail->urlFor($neverFound))->toBeNull()
        ->and($sent())->toBe(3);

    $this->travel(11)->minutes();
    $thumbnail->warm([$album, $track, $neverFound]);

    expect($sent())->toBe(4);

    $this->travel(1)->day();
    $thumbnail->warm([$album, $track, $neverFound]);

    expect($sent())->toBe(7);
});

it('warms the artwork of models without an uploaded image only', function () {
    fakeSpotifyThumbnails();
    $uploaded = Release::factory()->create(['cover_image' => 'release-covers/uploaded.jpg']);
    $release = Release::factory()->create(['spotify_url' => 'https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N']);
    $artist = Artist::factory()->create(['spotify_url' => 'https://open.spotify.com/artist/1ZMBY94RIDI1PLHrxY4iax']);
    $shortLink = Playlist::factory()->create(['spotify_url' => 'https://spotify.link/abc123']);

    app(SpotifyThumbnail::class)->warmArtwork([$uploaded, $release, null, $artist, $shortLink]);

    Http::assertSentCount(2);
    Http::assertSent(fn (Request $request): bool => $request['url'] === 'https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N');
    Http::assertSent(fn (Request $request): bool => $request['url'] === 'https://open.spotify.com/artist/1ZMBY94RIDI1PLHrxY4iax');
});

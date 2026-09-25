<?php

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

it('caches a thumbnail for 24 hours', function () {
    Http::fake([
        'open.spotify.com/oembed*' => Http::response(['thumbnail_url' => 'https://image-cdn-ak.spotifycdn.com/image/cover']),
    ]);
    $spotifyUrl = SpotifyUrl::parse('https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N');
    $thumbnail = app(SpotifyThumbnail::class);

    $thumbnail->urlFor($spotifyUrl);
    $this->travel(23)->hours();
    $thumbnail->urlFor($spotifyUrl);
    Http::assertSentCount(1);

    $this->travel(2)->hours();
    $thumbnail->urlFor($spotifyUrl);
    Http::assertSentCount(2);
});

it('returns null and remembers the failure for an hour when Spotify answers with an error', function () {
    Http::fake([
        'open.spotify.com/oembed*' => Http::response(['error' => 'not found'], 404),
    ]);
    $spotifyUrl = SpotifyUrl::parse('https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr');
    $thumbnail = app(SpotifyThumbnail::class);

    expect($thumbnail->urlFor($spotifyUrl))->toBeNull();
    $this->travel(59)->minutes();
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

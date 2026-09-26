<?php

use App\Models\Playlist;
use Illuminate\Support\Facades\Http;

it('uses the uploaded cover without calling Spotify', function () {
    $playlist = Playlist::factory()->make(['cover_image' => 'playlist-covers/ibiza.jpg']);

    expect($playlist->coverUrl())->toBe(asset('storage/playlist-covers/ibiza.jpg'));
    Http::assertNothingSent();
});

it('falls back to the Spotify playlist image', function () {
    fakeSpotifyThumbnails('https://image-cdn-ak.spotifycdn.com/image/ibiza');

    expect(Playlist::factory()->make(['cover_image' => null])->coverUrl())->toBe('https://image-cdn-ak.spotifycdn.com/image/ibiza');
});

it('titles a playlist converted without a title', function () {
    expect(Playlist::factory()->legacy()->make()->displayTitle())->toBe(Playlist::FALLBACK_TITLE)
        ->and(Playlist::factory()->make(['title' => 'Ibiza 2026'])->displayTitle())->toBe('Ibiza 2026');
});

it('lists the active playlists in the admin order', function () {
    Playlist::factory()->create(['title' => 'Second', 'order' => 2]);
    Playlist::factory()->create(['title' => 'First', 'order' => 1]);
    Playlist::factory()->inactive()->create(['title' => 'Hidden', 'order' => 0]);

    expect(Playlist::query()->active()->pluck('title')->all())->toBe(['First', 'Second']);
});

it('gives the footer player the playlist URI when Spotify can play it', function (?string $spotifyUrl, ?string $playUri) {
    expect(Playlist::factory()->make(['spotify_url' => $spotifyUrl])->playUri())->toBe($playUri);
})->with([
    'playlist link' => ['https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO?si=abc', 'spotify:playlist:28I7hCUFTyqblhgu5yGkOO'],
    'short link' => ['https://spotify.link/aBcD3fGh1j', null],
    'no link' => [null, null],
]);

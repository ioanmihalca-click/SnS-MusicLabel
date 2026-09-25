<?php

use App\Models\Artist;
use App\Models\Photo;
use App\Models\Playlist;
use App\Models\Release;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Stats Strip caches aggregate counts; clear so each test sees fresh seeds.
    Cache::forget('homepage.stats-strip');
});

it('responds 200 for the homepage', function () {
    $this->get('/')->assertOk();
});

it('renders every documented homepage section so nothing silently disappears', function () {
    fakeSpotifyThumbnails('https://image-cdn-ak.spotifycdn.com/image/featured-one');
    Artist::factory()->create(['name' => 'Test Artist', 'order' => 1]);
    Release::factory()->create([
        'title' => 'Test Release',
        'spotify_url' => 'https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N',
    ]);
    Playlist::factory()->create([
        'order' => 1,
        'spotify_url' => 'https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO',
    ]);
    Photo::factory()->create(['title' => 'Photo One']);
    Release::factory()->featured()->create([
        'title' => 'Featured One',
        'artist_display' => 'Featured Artist',
        'cover_image' => null,
    ]);

    $response = $this->get('/');

    $response->assertOk();

    // Hero
    $response->assertSeeText('Welcome to');
    $response->assertSeeText('Music Management, Label and Music Production');

    // Featured release pill, with the Spotify thumbnail until a cover is uploaded
    $response->assertSeeText('Now Spinning');
    $response->assertSeeText('Featured One');
    $response->assertSeeText('Featured Artist');
    $response->assertSee('https://image-cdn-ak.spotifycdn.com/image/featured-one');

    // Stats Strip (Stage E) — labels unique to this section
    $response->assertSeeText('Genres');
    $response->assertSeeText('Established');

    // About
    $response->assertSeeText('About');

    // Artists
    $response->assertSeeText('Artists');
    $response->assertSeeText('Test Artist');

    // Releases, with the player rendered from spotify_url
    $response->assertSeeText('Check Our Releases');
    $response->assertSeeText('Releases');
    $response->assertSee('src="https://open.spotify.com/embed/album/3zifCl5R2DaZGEmrPNUM1N"', escape: false);

    // Playlists
    $response->assertSeeText('Our Curated Collections');
    $response->assertSee('src="https://open.spotify.com/embed/playlist/28I7hCUFTyqblhgu5yGkOO"', escape: false);

    // Photo Gallery
    $response->assertSeeText('Some photos of Our Artists');

    // Contact
    $response->assertSeeText('Contact Us');
    $response->assertSeeText('Stockholm & Romania', escape: false);
    $response->assertSeeText('Connect With Us');

    // Footer
    $response->assertSeeText('Quick Links');
    $response->assertSeeText('Click Studios Digital');
    $response->assertSeeText('All rights reserved');
});

it('exposes the JSON-LD organization schema', function () {
    $organization = collect(jsonLd($this->get('/'))['@graph'])->firstWhere('@type', 'Organization');

    expect($organization)->not->toBeNull()
        ->and($organization['foundingDate'])->toBe('2020');
});

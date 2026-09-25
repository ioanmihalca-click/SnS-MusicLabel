<?php

use App\Models\Artist;

it('generates the slug from the name', function (string $name, string $slug) {
    expect(Artist::factory()->create(['name' => $name])->slug)->toBe($slug);
})->with([
    'plain name' => ['Style da Kid', 'style-da-kid'],
    'ampersand' => ['G&S', 'g-and-s'],
]);

it('keeps the slug when the artist is renamed', function () {
    $artist = Artist::factory()->create(['name' => 'Snow n Stuff']);

    $artist->update(['name' => 'Snow N Stuff']);

    expect($artist->fresh()->slug)->toBe('snow-n-stuff');
});

it('adds a numeric suffix to a duplicate slug', function () {
    Artist::factory()->create(['name' => 'THK']);

    expect(Artist::factory()->create(['name' => 'thk'])->slug)->toBe('thk-2');
});

it('keeps an explicit slug', function () {
    expect(Artist::factory()->create(['name' => 'G&S', 'slug' => 'gs'])->slug)->toBe('gs');
});

it('uses the uploaded photo, or else the Spotify profile picture', function () {
    fakeSpotifyThumbnails('https://image-cdn-ak.spotifycdn.com/image/profile');

    $uploaded = Artist::factory()->make(['photo' => 'artist-photos/thk.jpg']);
    $fromSpotify = Artist::factory()->make(['photo' => null, 'spotify_url' => 'https://open.spotify.com/artist/6YOmRPkzjX9bbTl6Qi2WPy']);

    expect($uploaded->photoUrl())->toBe(asset('storage/artist-photos/thk.jpg'))
        ->and($uploaded->hasUploadedArtwork())->toBeTrue()
        ->and($fromSpotify->photoUrl())->toBe('https://image-cdn-ak.spotifycdn.com/image/profile')
        ->and($fromSpotify->hasUploadedArtwork())->toBeFalse();
});

it('links the press kit only when one is uploaded', function () {
    expect(Artist::factory()->make(['press_kit' => 'press-kits/thk.pdf'])->pressKitUrl())->toBe(asset('storage/press-kits/thk.pdf'))
        ->and(Artist::factory()->make(['press_kit' => null])->pressKitUrl())->toBeNull();
});

it('lists only the profiles that are filled in', function () {
    $artist = Artist::factory()->make([
        'spotify_url' => 'https://open.spotify.com/artist/6YOmRPkzjX9bbTl6Qi2WPy',
        'instagram_url' => null,
        'soundcloud_url' => 'https://soundcloud.com/thk',
        'beatport_url' => '',
    ]);

    expect($artist->profileUrls())->toBe([
        'Spotify' => 'https://open.spotify.com/artist/6YOmRPkzjX9bbTl6Qi2WPy',
        'SoundCloud' => 'https://soundcloud.com/thk',
    ]);
});

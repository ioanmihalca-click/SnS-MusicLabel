<?php

use App\Models\Artist;
use App\Models\Release;
use Illuminate\Support\Facades\Http;

it('generates the slug from the title', function (string $title, string $slug) {
    expect(Release::factory()->create(['title' => $title])->slug)->toBe($slug);
})->with([
    'plain title' => ['Speak To Me', 'speak-to-me'],
    'ampersand' => ['G&S Back to Black', 'g-and-s-back-to-black'],
    'title without letters' => ['°°°', 'untitled'],
]);

it('keeps the slug when the title is edited', function () {
    $release = Release::factory()->create(['title' => 'Snow N Stuff - Fuego']);

    $release->update(['title' => 'Fuego']);

    expect($release->fresh()->slug)->toBe('snow-n-stuff-fuego');
});

it('adds a numeric suffix to a duplicate slug', function () {
    Release::factory()->create(['title' => 'Fuego']);

    expect(Release::factory()->create(['title' => 'Fuego'])->slug)->toBe('fuego-2')
        ->and(Release::factory()->create(['title' => 'Fuego'])->slug)->toBe('fuego-3');
});

it('regenerates an emptied slug from the current title', function () {
    $release = Release::factory()->create(['title' => 'Galaxy']);

    $release->update(['slug' => null, 'title' => 'Galaxy (Edit)']);

    expect($release->fresh()->slug)->toBe('galaxy-edit');
});

it('stores the canonical Spotify URL whatever shape is pasted', function () {
    $release = Release::factory()->create([
        'spotify_url' => 'https://open.spotify.com/intl-de/album/3zifCl5R2DaZGEmrPNUM1N?si=6e8c4ffed0314a6d',
    ]);

    expect($release->fresh()->spotify_url)->toBe('https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N');
});

it('lists featured releases only, newest first', function () {
    $older = Release::factory()->featured()->create(['released_at' => '2025-01-03']);
    $newest = Release::factory()->featured()->create(['released_at' => '2026-02-27']);
    $sameDayLater = Release::factory()->featured()->create(['released_at' => '2025-01-03']);
    Release::factory()->create(['released_at' => '2026-09-01']);

    expect(Release::featured()->pluck('id')->all())->toBe([$newest->id, $sameDayLater->id, $older->id]);
});

it('prefers the typed credit over the linked artists', function () {
    $release = Release::factory()->create(['artist_display' => 'THK & Pacha Man']);
    $release->artists()->attach(Artist::factory()->create(['name' => 'THK']));

    expect($release->credit)->toBe('THK & Pacha Man');
});

it('credits the linked artists in roster order when no credit is typed', function () {
    $release = Release::factory()->create(['artist_display' => null]);
    $release->artists()->attach([
        Artist::factory()->create(['name' => 'THK', 'order' => 3])->id,
        Artist::factory()->create(['name' => 'Snow N Stuff', 'order' => 0])->id,
        Artist::factory()->create(['name' => 'G&S', 'order' => 1])->id,
    ]);

    expect($release->credit)->toBe('Snow N Stuff, G&S & THK');
});

it('uses the uploaded cover without calling Spotify', function () {
    $release = Release::factory()->make(['cover_image' => 'release-covers/fuego.jpg']);

    expect($release->coverUrl())->toBe(asset('storage/release-covers/fuego.jpg'));
    Http::assertNothingSent();
});

it('falls back to the Spotify thumbnail when no cover is uploaded', function () {
    Http::fake([
        'open.spotify.com/oembed*' => Http::response(['thumbnail_url' => 'https://image-cdn-ak.spotifycdn.com/image/fuego']),
    ]);
    $release = Release::factory()->make([
        'cover_image' => null,
        'spotify_url' => 'https://open.spotify.com/album/7kRBMHJQEsklIRTNH0qRfp',
    ]);

    expect($release->coverUrl())->toBe('https://image-cdn-ak.spotifycdn.com/image/fuego');
});

it('has no cover without an upload or a Spotify link', function () {
    $release = Release::factory()->make(['cover_image' => null, 'spotify_url' => null]);

    expect($release->coverUrl())->toBeNull();
    Http::assertNothingSent();
});

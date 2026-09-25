<?php

use App\Enums\Genre;
use App\Enums\ReleaseFormat;
use App\Models\Artist;
use App\Models\Release;
use App\Models\Track;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    config(['app.url' => 'https://snow-n-stuff.com']);
    fakeSpotifyThumbnails('https://image-cdn-ak.spotifycdn.com/image/speak-to-me');
});

/**
 * @return array{release: Release, artist: Artist}
 */
function speakToMe(array $attributes = []): array
{
    $artist = Artist::factory()->create(['name' => 'Snow N Stuff']);
    $release = Release::factory()->create([
        'title' => 'Speak To Me',
        'released_at' => '2025-04-04',
        'format' => ReleaseFormat::Single,
        'genre' => Genre::MelodicTechno,
        'description' => '<p>Charting at #59 on Beatport Hype, backed by G&amp;S.</p>',
        'spotify_url' => 'https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N',
        'support' => ['Richie Hawtin', 'Don Diablo'],
        'chart_position' => '#59',
        'chart_name' => 'Beatport Hype',
        ...$attributes,
    ]);
    $release->artists()->attach($artist);
    Track::factory()->for($release)->create(['position' => 1, 'title' => 'Speak To Me', 'version' => 'Edit', 'duration_seconds' => 225, 'isrc' => 'SE6XY2500001']);
    Track::factory()->for($release)->create(['position' => 2, 'title' => 'Speak To Me', 'duration_seconds' => 404]);

    return ['release' => $release, 'artist' => $artist];
}

it('shows the release with its credit, listen links, tracklist, story and support', function () {
    speakToMe();

    $this->get('/releases/speak-to-me')
        ->assertOk()
        ->assertSee('<h1', escape: false)
        ->assertSeeText('Speak To Me')
        ->assertSee('/artists/snow-n-stuff"', escape: false)
        ->assertSeeText('Listen now')
        ->assertSee('href="https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N"', escape: false)
        ->assertSeeInOrder(['Tracklist', 'Edit', '3:45', '6:44'])
        ->assertSee('<time datetime="PT3M45S"', escape: false)
        ->assertSeeText('Charting at #59 on Beatport Hype, backed by G&S.')
        ->assertSeeInOrder(['Support', '#59', 'Beatport Hype', 'Richie Hawtin', 'Don Diablo'])
        ->assertSeeInOrder(['Released', '04.04.2025', 'Format', 'Single · 2 tracks', 'Genre', 'Melodic Techno']);
});

it('leads "Listen now" to the smartlink when there is one', function () {
    speakToMe(['smartlink_url' => 'https://ditto.fm/speak-to-me']);

    $this->get('/releases/speak-to-me')
        ->assertOk()
        ->assertSeeInOrder(['href="https://ditto.fm/speak-to-me"', 'Listen now', 'href="https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N"', 'Spotify'], escape: false);
});

it('links the roster artists inside a typed credit', function () {
    $thk = Artist::factory()->create(['name' => 'THK']);
    $release = Release::factory()->create(['title' => 'Warrior', 'artist_display' => 'THK & Pacha Man']);
    $release->artists()->attach($thk);

    $this->get('/releases/warrior')
        ->assertOk()
        ->assertSee('/artists/thk" class="border-b border-rule2 transition-colors hover:border-frost">THK</a> &amp; Pacha Man</p>', escape: false);
});

it('suggests more releases by the same artist', function () {
    ['artist' => $artist] = speakToMe();
    Release::factory()->create(['title' => 'Fuego'])->artists()->attach($artist);
    Release::factory()->create(['title' => 'Nubian Heat']);

    $this->get('/releases/speak-to-me')
        ->assertOk()
        ->assertSeeText('More from Snow N Stuff')
        ->assertSeeText('Fuego')
        ->assertDontSeeText('Nubian Heat');
});

it('renders a legacy release without date, format, genre or tracks', function () {
    Release::factory()->legacy()->create(['title' => 'Old Release', 'description' => null]);

    $this->get('/releases/old-release')
        ->assertOk()
        ->assertSeeText('Old Release')
        ->assertSeeText('The tracklist will be added soon.')
        ->assertDontSeeText('Released');
});

it('describes the release as a MusicAlbum released digitally on the label', function () {
    speakToMe();

    $graph = collect(jsonLd($this->get('/releases/speak-to-me'))['@graph']);
    $album = $graph->firstWhere('@type', 'MusicAlbum');

    expect($album['@id'])->toBe('https://snow-n-stuff.com/releases/speak-to-me#album')
        ->and($album['name'])->toBe('Speak To Me')
        ->and($album['description'])->toBe('Charting at #59 on Beatport Hype, backed by G&S.')
        ->and($album['image'])->toBe('https://image-cdn-ak.spotifycdn.com/image/speak-to-me')
        ->and($album['datePublished'])->toBe('2025-04-04')
        ->and($album['genre'])->toBe('Melodic Techno')
        ->and($album['albumReleaseType'])->toBe('https://schema.org/SingleRelease')
        ->and($album)->not->toHaveKey('albumProductionType')
        ->and($album['byArtist'])->toBe([[
            '@type' => 'MusicGroup',
            '@id' => 'https://snow-n-stuff.com/artists/snow-n-stuff#artist',
            'name' => 'Snow N Stuff',
            'url' => 'https://snow-n-stuff.com/artists/snow-n-stuff',
        ]])
        ->and($album['numTracks'])->toBe(2)
        ->and($album['track'][0])->toBe([
            '@type' => 'MusicRecording',
            'name' => 'Speak To Me (Edit)',
            'position' => 1,
            'duration' => 'PT3M45S',
            'isrcCode' => 'SE6XY2500001',
            'inAlbum' => ['@id' => 'https://snow-n-stuff.com/releases/speak-to-me#album'],
        ])
        ->and($album['track'][1])->not->toHaveKey('isrcCode')
        ->and($album['albumRelease'][0])->toMatchArray([
            '@type' => 'MusicRelease',
            'musicReleaseFormat' => 'https://schema.org/DigitalFormat',
            'recordLabel' => ['@id' => 'https://snow-n-stuff.com/#organization'],
        ])
        ->and(collect($graph->firstWhere('@type', 'BreadcrumbList')['itemListElement'])->pluck('item')->all())->toBe([
            'https://snow-n-stuff.com/',
            'https://snow-n-stuff.com/releases',
            'https://snow-n-stuff.com/releases/speak-to-me',
        ]);
});

it('marks a compilation as an album release of the compilation type', function () {
    Release::factory()->create(['title' => 'Frozen in Time', 'format' => ReleaseFormat::Compilation]);

    $album = collect(jsonLd($this->get('/releases/frozen-in-time'))['@graph'])->firstWhere('@type', 'MusicAlbum');

    expect($album['albumReleaseType'])->toBe('https://schema.org/AlbumRelease')
        ->and($album['albumProductionType'])->toBe('https://schema.org/CompilationAlbum');
});

it('shares the Spotify thumbnail as a small card and an uploaded cover as a large one', function (?string $coverImage, Closure $image, string $card) {
    speakToMe(['cover_image' => $coverImage]);

    $this->get('/releases/speak-to-me')
        ->assertOk()
        ->assertSee('<title>Speak To Me by Snow N Stuff - Snow &#039;n&#039; Stuff</title>', escape: false)
        ->assertSee('<link rel="canonical" href="https://snow-n-stuff.com/releases/speak-to-me">', escape: false)
        ->assertSee('<meta property="og:image" content="'.$image().'">', escape: false)
        ->assertSee('<meta name="twitter:card" content="'.$card.'">', escape: false);
})->with([
    'Spotify thumbnail' => [null, fn () => 'https://image-cdn-ak.spotifycdn.com/image/speak-to-me', 'summary'],
    'uploaded cover' => ['release-covers/speak-to-me.jpg', fn () => asset('storage/release-covers/speak-to-me.jpg'), 'summary_large_image'],
]);

it('loads the page with a fixed number of queries', function () {
    ['artist' => $artist] = speakToMe();
    Release::factory()->count(5)->create()->each(fn (Release $release) => $release->artists()->attach($artist));

    DB::enableQueryLog();
    $this->get('/releases/speak-to-me')->assertOk();

    // Session, release, artists, tracks, more releases and their artists; cache lookups excluded.
    expect(collect(DB::getQueryLog())->pluck('query')->reject(fn (string $query): bool => str_contains($query, '"cache"')))
        ->toHaveCount(5);
});

it('serves the release as Markdown with its title and tracklist', function () {
    speakToMe();

    $markdown = $this->get('/releases/speak-to-me.md')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
        ->getContent();

    expect($markdown)
        ->toContain('# Speak To Me')
        ->toContain('Released: 04.04.2025')
        ->toContain('Speak To Me (Edit) 3:45')
        ->toContain('backed by G&S.')
        ->not->toContain('&amp;')
        ->not->toContain('Web application by');
});

it('returns 404 for an unknown release', function () {
    $this->get('/releases/nope')->assertNotFound();
});

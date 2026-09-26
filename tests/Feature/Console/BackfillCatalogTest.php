<?php

use App\Enums\Genre;
use App\Enums\ReleaseFormat;
use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Release;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

const THE_CHANGE_ID = '3kxXDXxBbNYcwoKJasfW8X';

/**
 * @return array<string, mixed>
 */
function catalogFixture(): array
{
    return [
        'releases' => [
            THE_CHANGE_ID => [
                'type' => 'track',
                'legacy_title' => 'Snow N Stuff - °The Change°',
                'title' => 'The Change',
                'artist_display' => null,
                'artists' => ['snow-n-stuff'],
                'released_at' => '2026-05-08',
                'format' => 'single',
                'genre' => 'techno',
                'smartlink_url' => null,
                'support' => ['Paul van Dyk'],
                'chart_position' => null,
                'chart_name' => null,
                'description' => '<p>Curated description.</p>',
                'tracks' => [
                    ['title' => 'The Change', 'version' => 'Edit', 'duration' => '3:05', 'isrc' => null],
                    ['title' => 'The Change', 'version' => null, 'duration' => '4:40', 'isrc' => null],
                ],
            ],
        ],
        'artists' => [
            'Snow N Stuff' => [
                'legacy_name' => 'Snow n Stuff',
                'order' => 5,
                'spotify_url' => 'https://open.spotify.com/artist/6wIX9hW2uQAVv190xXV9mA',
                'description' => '<p>Curated bio.</p>',
                'role' => 'Melodic techno project',
                'origin' => 'Northern Europe · since 2019',
                'highlights' => ['The Change premiered by Paul van Dyk'],
                'instagram_url' => null,
                'soundcloud_url' => null,
                'beatport_url' => null,
            ],
        ],
        'playlists' => [
            '28I7hCUFTyqblhgu5yGkOO' => [
                'order' => 9,
                'title' => 'Vocal Deep House 2026',
                'description' => 'Melodic Techno · Ibiza',
            ],
        ],
    ];
}

/**
 * @param  array<string, mixed>  $catalog
 */
function writeCatalog(array $catalog): string
{
    $path = catalogFixtureDirectory().'/'.Str::uuid().'.json';
    File::ensureDirectoryExists(catalogFixtureDirectory());
    File::put($path, json_encode($catalog, JSON_THROW_ON_ERROR));

    return $path;
}

function catalogFixtureDirectory(): string
{
    return sys_get_temp_dir().'/sns-backfill-catalog-test';
}

/**
 * The rows as production has them right after the stage 1 migrations.
 *
 * @return array{release: Release, artist: Artist, playlist: Playlist}
 */
function legacyCatalogRows(): array
{
    return [
        'release' => Release::factory()->legacy()->create([
            'title' => 'Snow N Stuff - °The Change°',
            'description' => '<p>Live description.</p>',
            'spotify_url' => 'https://open.spotify.com/track/'.THE_CHANGE_ID,
        ]),
        'artist' => Artist::factory()->legacy()->create([
            'name' => 'Snow n Stuff',
            'order' => 0,
            'spotify_url' => 'https://open.spotify.com/artist/6wIX9hW2uQAVv190xXV9mA?si=WaUucV3URJaHkzoF1ct57w',
            'description' => '<p>Live bio.</p>',
        ]),
        'playlist' => Playlist::factory()->legacy()->create([
            'spotify_url' => 'https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO',
            'order' => 1,
        ]),
    ];
}

/**
 * @return array<string, mixed>
 */
function catalogDatabaseState(): array
{
    return collect(['releases', 'artists', 'playlists', 'tracks', 'artist_release'])
        ->mapWithKeys(fn (string $table): array => [$table => DB::table($table)->orderBy('id')->get()->toArray()])
        ->all();
}

afterEach(function () {
    File::deleteDirectory(catalogFixtureDirectory());
});

it('fills empty fields, cleans legacy titles and names, and links tracks and artists', function () {
    ['release' => $release, 'artist' => $artist, 'playlist' => $playlist] = legacyCatalogRows();
    $path = writeCatalog(catalogFixture());

    $this->artisan('catalog:backfill', ['--path' => $path])
        ->expectsOutputToContain('Releases: 1 matched, 0 not found.')
        ->expectsOutputToContain('Artists: 1 matched, 0 not found.')
        ->expectsOutputToContain('Playlists: 1 matched, 0 not found.')
        ->assertSuccessful();

    $release->refresh();
    expect($release)
        ->title->toBe('The Change')
        ->slug->toBe('the-change')
        ->released_at->toDateString()->toBe('2026-05-08')
        ->format->toBe(ReleaseFormat::Single)
        ->genre->toBe(Genre::Techno)
        ->support->toBe(['Paul van Dyk'])
        ->description->toBe('<p>Live description.</p>');
    expect($release->tracks->map->only(['position', 'version', 'duration_seconds'])->all())->toBe([
        ['position' => 1, 'version' => 'Edit', 'duration_seconds' => 185],
        ['position' => 2, 'version' => null, 'duration_seconds' => 280],
    ]);
    expect($release->artists->modelKeys())->toBe([$artist->id]);

    expect($artist->refresh())
        ->name->toBe('Snow N Stuff')
        ->slug->toBe('snow-n-stuff')
        ->order->toBe(0)
        ->role->toBe('Melodic techno project')
        ->highlights->toBe(['The Change premiered by Paul van Dyk'])
        ->description->toBe('<p>Live bio.</p>')
        ->spotify_url->toBe('https://open.spotify.com/artist/6wIX9hW2uQAVv190xXV9mA?si=WaUucV3URJaHkzoF1ct57w');

    expect($playlist->refresh())
        ->title->toBe('Vocal Deep House 2026')
        ->description->toBe('Melodic Techno · Ibiza')
        ->order->toBe(1);
});

it('changes nothing on a second run', function () {
    legacyCatalogRows();
    $path = writeCatalog(catalogFixture());
    $this->artisan('catalog:backfill', ['--path' => $path])->assertSuccessful();
    $afterFirstRun = catalogDatabaseState();
    $this->travel(1)->hour();

    $this->artisan('catalog:backfill', ['--path' => $path])->assertSuccessful();

    expect(catalogDatabaseState())->toEqual($afterFirstRun);
});

it('keeps values an admin already set', function () {
    ['release' => $release, 'artist' => $artist] = legacyCatalogRows();
    $release->update(['title' => 'The Change (Radio Edit)', 'genre' => Genre::House]);
    $release->tracks()->create(['position' => 1, 'title' => 'Admin Track', 'duration_seconds' => 200]);
    $otherArtist = Artist::factory()->create(['name' => 'THK']);
    $release->artists()->attach($otherArtist);
    $artist->update(['name' => 'SNS', 'role' => 'Duo']);

    $this->artisan('catalog:backfill', ['--path' => writeCatalog(catalogFixture())])->assertSuccessful();

    expect($release->refresh())
        ->title->toBe('The Change (Radio Edit)')
        ->slug->toBe('snow-n-stuff-the-change')
        ->genre->toBe(Genre::House)
        ->released_at->toDateString()->toBe('2026-05-08');
    expect($release->tracks->pluck('title')->all())->toBe(['Admin Track'])
        ->and($release->artists->modelKeys())->toBe([$otherArtist->id]);
    expect($artist->refresh())
        ->name->toBe('SNS')
        ->role->toBe('Duo')
        ->origin->toBe('Northern Europe · since 2019');
});

it('replaces filled values with --overwrite', function () {
    ['release' => $release, 'artist' => $artist, 'playlist' => $playlist] = legacyCatalogRows();
    $release->update(['title' => 'The Change (Radio Edit)', 'genre' => Genre::House, 'is_featured' => true]);
    $release->tracks()->create(['position' => 1, 'title' => 'Admin Track', 'duration_seconds' => 200]);
    $release->artists()->attach(Artist::factory()->create(['name' => 'THK']));
    $artist->update(['name' => 'SNS']);
    $playlist->update(['title' => 'Old Title']);

    $this->artisan('catalog:backfill', ['--path' => writeCatalog(catalogFixture()), '--overwrite' => true])->assertSuccessful();

    expect($release->refresh())
        ->title->toBe('The Change')
        ->slug->toBe('the-change')
        ->genre->toBe(Genre::Techno)
        ->description->toBe('<p>Curated description.</p>')
        ->is_featured->toBeTrue();
    expect($release->tracks->pluck('duration_seconds')->all())->toBe([185, 280])
        ->and($release->artists->modelKeys())->toBe([$artist->id]);
    expect($artist->refresh())
        ->name->toBe('Snow N Stuff')
        ->order->toBe(5);
    expect($playlist->refresh())
        ->title->toBe('Vocal Deep House 2026')
        ->order->toBe(9);
});

it('reports what would change without saving on --dry-run', function () {
    legacyCatalogRows();
    $before = catalogDatabaseState();

    $this->artisan('catalog:backfill', ['--path' => writeCatalog(catalogFixture()), '--dry-run' => true])
        ->expectsOutputToContain('tracks (2)')
        ->expectsOutputToContain('Dry run: nothing was saved.')
        ->assertSuccessful();

    expect(catalogDatabaseState())->toEqual($before);
});

it('reports catalogue entries missing from the database', function () {
    $this->artisan('catalog:backfill', ['--path' => writeCatalog(catalogFixture())])
        ->expectsOutputToContain('Releases: 0 matched, 1 not found.')
        ->expectsOutputToContain('Not found: track/'.THE_CHANGE_ID.' (The Change)')
        ->assertSuccessful();
});

it('writes nothing when any entry is invalid', function (string $key, mixed $value) {
    legacyCatalogRows();
    $before = catalogDatabaseState();
    $catalog = catalogFixture();
    data_set($catalog, $key, $value);

    $this->artisan('catalog:backfill', ['--path' => writeCatalog($catalog)])
        ->expectsOutputToContain('is invalid. Nothing was changed.')
        ->assertFailed();

    expect(catalogDatabaseState())->toEqual($before);
})->with([
    'unknown genre' => ['releases.'.THE_CHANGE_ID.'.genre', 'dubstep'],
    'unknown format' => ['releases.'.THE_CHANGE_ID.'.format', 'mixtape'],
    'date not in Y-m-d' => ['releases.'.THE_CHANGE_ID.'.released_at', '08/05/2026'],
    'duration not in m:ss' => ['releases.'.THE_CHANGE_ID.'.tracks.1.duration', '4m40'],
    'track without a title' => ['releases.'.THE_CHANGE_ID.'.tracks.0.title', null],
    'playlist without a title' => ['playlists.28I7hCUFTyqblhgu5yGkOO.title', ''],
    'artist link that is not a URL' => ['artists.Snow N Stuff.instagram_url', 'instagram'],
]);

it('rejects release keys that are not Spotify IDs', function () {
    $catalog = catalogFixture();
    $catalog['releases']['not-an-id'] = $catalog['releases'][THE_CHANGE_ID];

    $this->artisan('catalog:backfill', ['--path' => writeCatalog($catalog)])
        ->expectsOutputToContain('releases: [not-an-id] is not a Spotify ID.')
        ->assertFailed();
});

it('fails when the catalogue file is missing', function () {
    $this->artisan('catalog:backfill', ['--path' => sys_get_temp_dir().'/missing-catalog.json'])
        ->expectsOutputToContain('Catalogue file not found')
        ->assertFailed();
});

it('validates the shipped catalogue', function () {
    $this->artisan('catalog:backfill', ['--dry-run' => true])
        ->expectsOutputToContain('Releases: 0 matched, 19 not found.')
        ->assertSuccessful();
});

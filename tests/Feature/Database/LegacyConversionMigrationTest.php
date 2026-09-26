<?php

use App\Support\Spotify\SpotifyUrl;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * The test database is fully migrated, so each test puts back the legacy
 * columns and table (as production has them before stage 1) and then runs the
 * conversion (step B) and the guarded drop (step D) by hand.
 */
function recreateLegacySpotifySchema(): void
{
    Schema::table('releases', function (Blueprint $table) {
        $table->text('spotify_embed_code')->nullable();
    });

    Schema::table('playlists', function (Blueprint $table) {
        $table->text('spotify_embed_url')->nullable();
    });

    Schema::create('featured_tracks', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('artist_name');
        $table->string('spotify_track_url');
        $table->string('cover_image')->nullable();
        $table->date('released_at')->nullable();
        $table->unsignedInteger('order')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

function stageOneMigration(string $name): Migration
{
    return require collect(File::glob(database_path("migrations/*_{$name}.php")))->sole();
}

function legacyEmbed(string $type, string $id): string
{
    return '<iframe style="border-radius:12px" src="https://open.spotify.com/embed/'.$type.'/'.$id.'?utm_source=generator" width="100%" height="352" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>';
}

function insertLegacyRelease(string $title, ?string $embedCode): int
{
    return DB::table('releases')->insertGetId([
        'title' => $title,
        'spotify_embed_code' => $embedCode,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

beforeEach(function () {
    recreateLegacySpotifySchema();
});

it('converts legacy embed codes into canonical Spotify URLs', function () {
    $track = insertLegacyRelease('Snow N Stuff - °The Change°', legacyEmbed('track', '3kxXDXxBbNYcwoKJasfW8X'));
    $album = insertLegacyRelease('Snow N Stuff - Fuego', legacyEmbed('album', '7kRBMHJQEsklIRTNH0qRfp'));
    $empty = insertLegacyRelease('No embed yet', '');
    $playlist = DB::table('playlists')->insertGetId([
        'spotify_embed_url' => legacyEmbed('playlist', '28I7hCUFTyqblhgu5yGkOO'),
        'order' => 1,
        'is_active' => true,
    ]);

    stageOneMigration('convert_legacy_spotify_embeds')->up();

    expect(DB::table('releases')->pluck('spotify_url', 'id')->all())->toBe([
        $track => 'https://open.spotify.com/track/3kxXDXxBbNYcwoKJasfW8X',
        $album => 'https://open.spotify.com/album/7kRBMHJQEsklIRTNH0qRfp',
        $empty => null,
    ]);
    expect(DB::table('playlists')->where('id', $playlist)->value('spotify_url'))
        ->toBe('https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO');
});

it('turns each featured track into a release', function () {
    DB::table('featured_tracks')->insert([
        [
            'title' => 'Human made',
            'artist_name' => 'Snow N Stuff',
            'spotify_track_url' => 'https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr?si=bce74a18c01f40b5',
            'cover_image' => 'featured-tracks/01KR785MPC9B3B7E7K37RYX2MK.jpg',
            'released_at' => null,
            'order' => 0,
            'is_active' => true,
            'created_at' => '2026-05-10 09:30:00',
            'updated_at' => '2026-05-11 10:00:00',
        ],
        [
            'title' => 'Retired Pick',
            'artist_name' => 'THK & Pacha Man',
            'spotify_track_url' => 'https://open.spotify.com/track/6tWjyrs1LLVVdIYIwRUQWB',
            'cover_image' => null,
            'released_at' => '2019-09-13',
            'order' => 1,
            'is_active' => false,
            'created_at' => '2026-05-10 09:30:00',
            'updated_at' => '2026-05-10 09:30:00',
        ],
    ]);

    stageOneMigration('convert_legacy_spotify_embeds')->up();

    $humanMade = DB::table('releases')->where('spotify_url', 'https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr')->sole();
    expect($humanMade)
        ->title->toBe('Human made')
        ->slug->toBe('human-made')
        ->artist_display->toBe('Snow N Stuff')
        ->cover_image->toBe('featured-tracks/01KR785MPC9B3B7E7K37RYX2MK.jpg')
        ->released_at->toBeNull()
        ->format->toBe('single')
        ->is_featured->toEqual(1)
        ->created_at->toBe('2026-05-10 09:30:00')
        ->updated_at->toBe('2026-05-11 10:00:00');
    expect(DB::table('releases')->where('title', 'Retired Pick')->value('is_featured'))->toEqual(0);
});

it('flags the matching release instead of duplicating it', function () {
    $release = insertLegacyRelease('Snow N Stuff - °The Change°', legacyEmbed('track', '3kxXDXxBbNYcwoKJasfW8X'));
    DB::table('featured_tracks')->insert([
        'title' => 'The Change',
        'artist_name' => 'Snow N Stuff',
        'spotify_track_url' => 'https://open.spotify.com/intl-de/track/3kxXDXxBbNYcwoKJasfW8X?si=abc',
        'is_active' => true,
    ]);

    stageOneMigration('convert_legacy_spotify_embeds')->up();

    expect(DB::table('releases')->count())->toBe(1)
        ->and(DB::table('releases')->where('id', $release)->value('is_featured'))->toEqual(1);
});

it('generates unique slugs for releases and artists', function () {
    $first = insertLegacyRelease('Fuego', legacyEmbed('album', '7kRBMHJQEsklIRTNH0qRfp'));
    $second = insertLegacyRelease('Fuego', legacyEmbed('track', '3kxXDXxBbNYcwoKJasfW8X'));
    $artist = DB::table('artists')->insertGetId([
        'name' => 'G&S',
        'order' => 1,
        'spotify_url' => 'https://open.spotify.com/artist/1ZMBY94RIDI1PLHrxY4iax',
        'description' => 'Duo',
    ]);

    stageOneMigration('convert_legacy_spotify_embeds')->up();

    expect(DB::table('releases')->whereIn('id', [$first, $second])->orderBy('id')->pluck('slug')->all())->toBe(['fuego', 'fuego-2'])
        ->and(DB::table('artists')->where('id', $artist)->value('slug'))->toBe('g-and-s');
});

it('changes nothing when the conversion runs twice', function () {
    insertLegacyRelease('Snow N Stuff - Fuego', legacyEmbed('album', '7kRBMHJQEsklIRTNH0qRfp'));
    DB::table('featured_tracks')->insert([
        'title' => 'Human made',
        'artist_name' => 'Snow N Stuff',
        'spotify_track_url' => 'https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr',
        'is_active' => true,
    ]);
    $conversion = stageOneMigration('convert_legacy_spotify_embeds');
    $conversion->up();
    $afterFirstRun = DB::table('releases')->orderBy('id')->get()->toArray();

    $conversion->up();

    expect(DB::table('releases')->orderBy('id')->get()->toArray())->toEqual($afterFirstRun);
});

it('refuses to drop the legacy columns while an embed could not be converted', function () {
    insertLegacyRelease('Snow N Stuff - Fuego', legacyEmbed('album', '7kRBMHJQEsklIRTNH0qRfp'));
    $soundCloud = insertLegacyRelease('SoundCloud only', '<iframe src="https://w.soundcloud.com/player/?url=tracks/123"></iframe>');
    $shortLinkTrack = DB::table('featured_tracks')->insertGetId([
        'title' => 'Short link',
        'artist_name' => 'Snow N Stuff',
        'spotify_track_url' => 'https://spotify.link/aBcD3fGh1j',
        'is_active' => true,
    ]);
    stageOneMigration('convert_legacy_spotify_embeds')->up();

    expect(fn () => stageOneMigration('drop_legacy_spotify_embeds')->up())
        ->toThrow(RuntimeException::class, "releases #{$soundCloud}; featured_tracks #{$shortLinkTrack}");

    expect(Schema::hasColumn('releases', 'spotify_embed_code'))->toBeTrue()
        ->and(Schema::hasColumn('playlists', 'spotify_embed_url'))->toBeTrue()
        ->and(Schema::hasTable('featured_tracks'))->toBeTrue();
});

it('drops the legacy columns once converted and rebuilds them on rollback', function () {
    $release = insertLegacyRelease('Snow N Stuff - Fuego', legacyEmbed('album', '7kRBMHJQEsklIRTNH0qRfp'));
    $playlist = DB::table('playlists')->insertGetId([
        'spotify_embed_url' => legacyEmbed('playlist', '28I7hCUFTyqblhgu5yGkOO'),
        'order' => 1,
        'is_active' => true,
    ]);
    DB::table('featured_tracks')->insert([
        'title' => 'Human made',
        'artist_name' => 'Snow N Stuff',
        'spotify_track_url' => 'https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr',
        'cover_image' => 'featured-tracks/human-made.jpg',
        'is_active' => true,
    ]);
    stageOneMigration('convert_legacy_spotify_embeds')->up();
    $drop = stageOneMigration('drop_legacy_spotify_embeds');

    $drop->up();

    expect(Schema::hasColumn('releases', 'spotify_embed_code'))->toBeFalse()
        ->and(Schema::hasColumn('playlists', 'spotify_embed_url'))->toBeFalse()
        ->and(Schema::hasTable('featured_tracks'))->toBeFalse();

    $drop->down();

    $rebuiltRelease = DB::table('releases')->where('id', $release)->value('spotify_embed_code');
    $rebuiltPlaylist = DB::table('playlists')->where('id', $playlist)->value('spotify_embed_url');
    expect(SpotifyUrl::parse($rebuiltRelease)->url())->toBe('https://open.spotify.com/album/7kRBMHJQEsklIRTNH0qRfp')
        ->and(SpotifyUrl::parse($rebuiltPlaylist)->url())->toBe('https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO')
        ->and(DB::table('featured_tracks')->sole())
        ->title->toBe('Human made')
        ->artist_name->toBe('Snow N Stuff')
        ->spotify_track_url->toBe('https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr')
        ->cover_image->toBe('featured-tracks/human-made.jpg')
        ->is_active->toEqual(1);
});

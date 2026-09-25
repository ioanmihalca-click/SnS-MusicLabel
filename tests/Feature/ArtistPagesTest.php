<?php

use App\Enums\Genre;
use App\Models\Artist;
use App\Models\Release;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['app.url' => 'https://snow-n-stuff.com']);
    fakeSpotifyThumbnails('https://image-cdn-ak.spotifycdn.com/image/artist');
});

function gAndS(array $attributes = []): Artist
{
    return Artist::factory()->create([
        'name' => 'G&S',
        'order' => 1,
        'role' => 'DJ / producer duo',
        'origin' => 'Glenn Forrestgate (SE) × Style da Kid (RO)',
        'description' => '<p>G&amp;S is a collaboration between Glenn Forrestgate and Style da Kid.</p>',
        'spotify_url' => 'https://open.spotify.com/artist/1ZMBY94RIDI1PLHrxY4iax',
        'instagram_url' => 'https://www.instagram.com/gands',
        'highlights' => ['Back to Black reached #15 on Music Week Upfront (UK)'],
        ...$attributes,
    ]);
}

it('lists the roster in order', function () {
    Artist::factory()->create(['name' => 'THK', 'order' => 2, 'role' => 'Duo']);
    gAndS();
    Artist::factory()->legacy()->create(['name' => 'Snow N Stuff', 'order' => 0]);

    $this->get('/artists')
        ->assertOk()
        ->assertSeeInOrder(['Snow N Stuff', 'G&amp;S', 'DJ / producer duo', 'THK', 'Duo'], escape: false)
        ->assertSee('/artists/g-and-s"', escape: false);
});

it('shows the artist with bio, highlights, profiles and discography', function () {
    $artist = gAndS();
    $newer = Release::factory()->create(['title' => 'Nubian Heat', 'released_at' => '2024-10-11', 'genre' => Genre::AfroHouse]);
    $older = Release::factory()->create(['title' => 'Back to Black', 'released_at' => '2024-03-08', 'genre' => Genre::DeepHouse]);
    $artist->releases()->attach([$older->id, $newer->id]);
    Release::factory()->create(['title' => 'Someone Else']);

    $this->get('/artists/g-and-s')
        ->assertOk()
        ->assertSeeInOrder(['DJ / producer duo', '<h1', 'G&amp;S', 'Glenn Forrestgate (SE) × Style da Kid (RO)'], escape: false)
        ->assertSeeText('Follow on Spotify')
        ->assertSee('href="https://www.instagram.com/gands"', escape: false)
        ->assertSee('<p>G&amp;S is a collaboration between Glenn Forrestgate and Style da Kid.</p>', escape: false)
        ->assertSeeInOrder(['Highlights', 'Back to Black reached #15 on Music Week Upfront (UK)'])
        ->assertSeeInOrder(['Discography', 'Nubian Heat', 'Back to Black'])
        ->assertDontSeeText('Someone Else')
        ->assertDontSeeText('Press kit');
});

it('links the press kit only once one is uploaded', function () {
    gAndS(['press_kit' => 'press-kits/gands.pdf']);

    $this->get('/artists/g-and-s')
        ->assertOk()
        ->assertSeeText('Press kit (PDF)')
        ->assertSee('href="'.asset('storage/press-kits/gands.pdf').'"', escape: false);
});

it('shows the rest of the roster', function () {
    gAndS();
    Artist::factory()->create(['name' => 'THK', 'order' => 2]);

    $this->get('/artists/g-and-s')
        ->assertOk()
        ->assertSeeInOrder(['Also on the label', 'THK']);
});

it('renders a legacy artist without role, origin or highlights', function () {
    Artist::factory()->legacy()->create(['name' => 'Style da Kid']);

    $this->get('/artists/style-da-kid')
        ->assertOk()
        ->assertSeeText('Style da Kid')
        ->assertDontSeeText('Highlights')
        ->assertDontSeeText('Discography');
});

it('uses the uploaded photo, or else the Spotify profile picture', function () {
    gAndS(['photo' => 'artist-photos/gands.jpg']);
    Artist::factory()->create(['name' => 'THK', 'photo' => null, 'spotify_url' => 'https://open.spotify.com/artist/6YOmRPkzjX9bbTl6Qi2WPy']);

    $this->get('/artists/g-and-s')
        ->assertOk()
        ->assertSee('<meta property="og:image" content="'.asset('storage/artist-photos/gands.jpg').'">', escape: false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', escape: false)
        ->assertSee('src="https://image-cdn-ak.spotifycdn.com/image/artist"', escape: false);

    Http::assertSent(fn ($request): bool => $request['url'] === 'https://open.spotify.com/artist/6YOmRPkzjX9bbTl6Qi2WPy');
});

it('describes the artist as a MusicGroup linked to their own profiles', function () {
    $artist = gAndS();
    $artist->releases()->attach(Release::factory()->create(['title' => 'Back to Black', 'genre' => Genre::DeepHouse]));

    $graph = collect(jsonLd($this->get('/artists/g-and-s'))['@graph']);
    $group = $graph->firstWhere('@type', 'MusicGroup');

    expect($group['@id'])->toBe('https://snow-n-stuff.com/artists/g-and-s#artist')
        ->and($group['name'])->toBe('G&S')
        ->and($group['url'])->toBe('https://snow-n-stuff.com/artists/g-and-s')
        ->and($group['description'])->toBe('G&S is a collaboration between Glenn Forrestgate and Style da Kid.')
        ->and($group['image'])->toBe('https://image-cdn-ak.spotifycdn.com/image/artist')
        ->and($group['sameAs'])->toBe([
            'https://open.spotify.com/artist/1ZMBY94RIDI1PLHrxY4iax',
            'https://www.instagram.com/gands',
        ])
        ->and($group['genre'])->toBe(['Deep House'])
        ->and($group['album'])->toBe([[
            '@type' => 'MusicAlbum',
            '@id' => 'https://snow-n-stuff.com/releases/back-to-black#album',
            'name' => 'Back to Black',
            'url' => 'https://snow-n-stuff.com/releases/back-to-black',
        ]])
        ->and($graph->firstWhere('@type', 'Organization')['sameAs'])->not->toContain('https://open.spotify.com/artist/1ZMBY94RIDI1PLHrxY4iax');
});

it('loads the artist page with a fixed number of queries', function () {
    $artist = gAndS();
    Release::factory()->count(4)->create()->each(fn (Release $release) => $release->artists()->attach($artist));
    Artist::factory()->count(3)->create();

    DB::enableQueryLog();
    $this->get('/artists/g-and-s')->assertOk();

    // Artist, releases, their artists and the rest of the roster; cache lookups excluded.
    expect(collect(DB::getQueryLog())->pluck('query')->reject(fn (string $query): bool => str_contains($query, '"cache"')))
        ->toHaveCount(4);
});

it('returns 404 for an unknown artist', function () {
    $this->get('/artists/nobody')->assertNotFound();
});

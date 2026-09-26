<?php

use App\Models\Artist;
use App\Models\Blog;
use App\Models\Photo;
use App\Models\Playlist;
use App\Models\Release;
use Dom\Element;
use Dom\HTMLDocument;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    fakeSpotifyThumbnails('https://image-cdn-ak.spotifycdn.com/image/thumbnail');
});

/**
 * The homepage's HTML as a document, to query with CSS selectors.
 */
function homeDocument(TestResponse $response): HTMLDocument
{
    return HTMLDocument::createFromString($response->getContent(), LIBXML_NOERROR);
}

/**
 * The link to the news post announced in the homepage hero, if any.
 */
function heroAnnouncement(TestResponse $response): ?Element
{
    return homeDocument($response)->querySelector('section[aria-labelledby="hero-title"] a[href^="'.route('blog.index').'/"]');
}

it('renders every section so nothing silently disappears', function () {
    $artist = Artist::factory()->create(['name' => 'G&S', 'order' => 1]);
    Artist::factory()->create(['name' => 'Style da Kid', 'order' => 2]);
    $featured = Release::factory()->featured()->create([
        'title' => 'Human Made',
        'released_at' => '2026-02-27',
        'cover_image' => 'release-covers/human-made.jpg',
    ]);
    $featured->artists()->attach($artist);
    Release::factory()->create([
        'title' => 'Speak To Me',
        'released_at' => '2025-04-04',
        'support' => ['Richie Hawtin', 'Don Diablo'],
        'chart_position' => '#59',
        'chart_name' => 'Beatport Hype',
    ])->artists()->attach($artist);
    Release::factory()->create(['title' => 'I Know', 'released_at' => now()->subWeek()]);
    Playlist::factory()->create(['title' => 'Vocal Deep House 2026', 'spotify_url' => 'https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO']);
    Photo::factory()->create(['title' => 'In the studio', 'image_path' => 'photos/studio.jpg']);
    Blog::factory()->published()->create(['title' => 'Pre-save all our future releases', 'slug' => 'pre-save']);

    $response = $this->get('/')->assertOk();

    $response
        // Hero: the featured release, its artwork, credit and listen link
        ->assertSeeInOrder(['Featured release', '27.02.2026', 'Human Made'])
        ->assertSee('src="'.asset('storage/release-covers/human-made.jpg').'"', escape: false)
        ->assertSee('fetchpriority="high"', escape: false)
        ->assertSee('href="'.route('artists.show', 'g-and-s').'"', escape: false)
        ->assertSeeText('Listen now')
        // Next release
        ->assertSeeInOrder(['New', 'I Know'])
        // Releases grid, with the artist shortcuts into the catalogue
        ->assertSeeInOrder(['Releases', 'Every release on the label, newest first.'])
        ->assertSee('href="'.route('releases.index', ['artist' => 'g-and-s']).'"', escape: false)
        ->assertSeeText('View all 3 releases')
        // Support and charts
        ->assertSeeInOrder(['Supported &amp; played by', 'Richie Hawtin', 'Don Diablo', '#59', 'Beatport Hype', 'Speak To Me · 2025'], escape: false)
        // Artists, in roster order
        ->assertSeeInOrder(['Artists', 'G&amp;S', 'Style da Kid'], escape: false)
        // Playlists, with Follow
        ->assertSeeText('Vocal Deep House 2026')
        ->assertSee('href="https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO"', escape: false)
        // About, with the photos leading to the full gallery
        ->assertSeeText('Music management, label and production for Tech House, Deep House, House and Techno.')
        ->assertSeeText('More than two and a half decades of A&amp;R', escape: false)
        ->assertSeeText('Management for THK · G&amp;S · Snow \'n\' Stuff · Style da Kid', escape: false)
        ->assertSee('alt="In the studio"', escape: false)
        ->assertSee('href="'.route('about').'#gallery"', escape: false)
        // News, releases and posts together
        ->assertSeeText('I Know is out now')
        ->assertSeeText('Pre-save all our future releases')
        // Follow
        ->assertSeeInOrder(['Stay in the loop', 'Spotify', 'Instagram', 'Facebook', 'X'])
        // Contacts
        ->assertSeeInOrder(['glenn@1namm.com', 'info@1namm.com', 'demo@1namm.com']);
});

it('has exactly one <h1>, no iframe and no second gallery', function () {
    Release::factory()->featured()->create();
    Photo::factory()->count(3)->create();

    $document = homeDocument($this->get('/')->assertOk());

    expect($document->querySelectorAll('h1'))->toHaveCount(1)
        ->and($document->querySelector('main > h1')->textContent)->toBe("Snow 'n' Stuff")
        ->and($document->querySelectorAll('iframe'))->toHaveCount(0)
        ->and($document->querySelectorAll('#gallery, [data-fancybox]'))->toHaveCount(0);
});

it('shows the eight newest releases, newest first', function () {
    foreach (range(1, 10) as $day) {
        Release::factory()->create(['title' => "Release {$day}", 'released_at' => now()->subYear()->addDays($day)]);
    }

    $cards = homeDocument($this->get('/')->assertOk())->querySelectorAll('#releases article');

    expect(array_map(fn (Element $card): string => trim($card->querySelector('div > a')->textContent), iterator_to_array($cards, false)))
        ->toBe(['Release 10', 'Release 9', 'Release 8', 'Release 7', 'Release 6', 'Release 5', 'Release 4', 'Release 3']);
});

it('works with an empty database', function () {
    $response = $this->get('/')->assertOk();

    $response->assertSeeText('Music management, label & production')
        ->assertSeeText('About the label')
        ->assertDontSeeText('Supported & played by')
        ->assertDontSee('fetchpriority="high"', escape: false)
        ->assertSeeText('Stay in the loop')
        ->assertSeeText('glenn@1namm.com');
});

it('falls back to the newest release when none is featured', function () {
    Release::factory()->create(['title' => 'Older', 'released_at' => '2024-01-01']);
    Release::factory()->create(['title' => 'Newest', 'released_at' => '2025-01-01']);

    $hero = homeDocument($this->get('/')->assertOk())->querySelector('#hero-title');

    expect(trim($hero->textContent))->toBe('Newest');
    $this->get('/')->assertSeeInOrder(['Latest release', '01.01.2025', 'Newest', 'New', 'Older']);
});

it('prefers the newest featured release', function () {
    Release::factory()->featured()->create(['title' => 'Old Feature', 'released_at' => '2023-01-01']);
    Release::factory()->featured()->create(['title' => 'New Feature', 'released_at' => '2024-01-01']);
    Release::factory()->create(['title' => 'Not Featured', 'released_at' => '2025-01-01']);

    $hero = homeDocument($this->get('/')->assertOk())->querySelector('#hero-title');

    expect(trim($hero->textContent))->toBe('New Feature');
});

it('announces an upcoming release with its date', function () {
    Release::factory()->featured()->create(['title' => 'Featured', 'released_at' => now()->subMonth()]);
    $upcoming = Release::factory()->create(['title' => 'Coming Soon', 'released_at' => now()->addMonth()]);

    $this->get('/')
        ->assertOk()
        ->assertSeeInOrder(['New', 'Coming Soon', 'Out '.$upcoming->released_at->format('d.m.Y')]);
});

it('keeps unreleased releases out of the news', function () {
    Release::factory()->create(['title' => 'Coming Soon', 'released_at' => now()->addMonth()]);

    $this->get('/')->assertOk()->assertDontSeeText('Coming Soon is out now');
});

it('exposes the label once in the JSON-LD graph', function () {
    $graph = collect(jsonLd($this->get('/'))['@graph']);

    expect($graph->where('@type', 'Organization'))->toHaveCount(1)
        ->and($graph->firstWhere('@type', 'Organization')['foundingDate'])->toBe('2020')
        ->and($graph->where('@type', 'WebSite'))->toHaveCount(1);
});

it('invites artists to send a demo, next to Follow', function () {
    $document = homeDocument($this->get('/')->assertOk());
    $demo = $document->querySelector('section[aria-labelledby="demo-title"]');
    $link = $demo->querySelector('a[href="'.route('demos').'"]');

    expect(trim($demo->querySelector('h2')->textContent))->toBe('Send us your demo')
        ->and($link)->not->toBeNull()
        ->and($link->hasAttribute('wire:navigate'))->toBeTrue()
        ->and($demo->nextElementSibling->getAttribute('aria-labelledby'))->toBe('follow-title');
});

it('looks up the Spotify thumbnails it shows in one round of parallel requests', function () {
    $lookups = recordSpotifyLookups();
    Release::factory()->count(8)->sequence(fn (Sequence $sequence): array => ['released_at' => now()->addDays($sequence->index + 1)])->create();
    Release::factory()->create(['title' => 'Only in the news', 'released_at' => now()->subMonth()]);
    Release::factory()->create(['released_at' => now()->subMonths(2), 'cover_image' => 'release-covers/uploaded.jpg']);
    Artist::factory()->create();
    Artist::factory()->create(['photo' => 'artist-photos/uploaded.jpg']);
    Playlist::factory()->create();
    Playlist::factory()->inactive()->create();

    $this->get('/')->assertOk()->assertSeeText('Only in the news is out now');

    // The eight upcoming releases, the one only in the news, an artist and a playlist.
    expect($lookups->getArrayCopy())->toBe(oneParallelRound(11));
});

it('announces a news post in one line above the hero', function () {
    Release::factory()->featured()->create(['title' => 'Human Made', 'released_at' => '2026-02-27']);
    Blog::factory()->published()->inHero('Meet us at ADE 2026 · Amsterdam, 21–25 October')->create(['slug' => 'ade-2026']);

    $response = $this->get('/')->assertOk();
    $link = heroAnnouncement($response);

    expect($link)->not->toBeNull()
        ->and(Str::squish($link->textContent))->toBe('Meet us at ADE 2026 · Amsterdam, 21–25 October →')
        ->and($link->getAttribute('href'))->toBe(route('blog.show', 'ade-2026'))
        ->and($link->hasAttribute('wire:navigate'))->toBeTrue()
        ->and(homeDocument($response)->querySelectorAll('h1'))->toHaveCount(1);
    $response->assertSeeInOrder(['Meet us at ADE 2026', 'Featured release', 'Human Made']);
    $this->get('/', ['Accept' => 'text/markdown'])
        ->assertOk()
        ->assertSee('[Meet us at ADE 2026 · Amsterdam, 21–25 October →]('.route('blog.show', 'ade-2026').')', escape: false);
});

it('announces the post by its title when it has no hero text, even without releases', function () {
    Blog::factory()->published()->inHero()->create(['title' => 'Speak To Me Is Out', 'slug' => 'speak-to-me-is-out']);

    $link = heroAnnouncement($this->get('/')->assertOk());

    expect(Str::squish($link->textContent))->toBe('Speak To Me Is Out →')
        ->and($link->getAttribute('href'))->toBe(route('blog.show', 'speak-to-me-is-out'));
});

it('drops the announcement on its own once its date has passed', function () {
    Blog::factory()->published()->inHero('Meet us at ADE 2026')->create();

    expect(heroAnnouncement($this->get('/')))->not->toBeNull();

    $this->travel(8)->days();

    expect(heroAnnouncement($this->get('/')->assertOk()))->toBeNull();
});

it('announces only published posts', function (string $state) {
    Blog::factory()->{$state}()->inHero('Meet us at ADE 2026')->create();

    $this->get('/')->assertOk()->assertDontSeeText('Meet us at ADE 2026');
})->with(['draft', 'scheduled']);

it('announces the newest published post when several are in the hero', function () {
    Blog::factory()->inHero('Older announcement')->create(['published_at' => now()->subWeek()]);
    Blog::factory()->inHero('Newer announcement')->create(['published_at' => now()->subDay()]);

    $link = heroAnnouncement($this->get('/')->assertOk());

    expect(Str::squish($link->textContent))->toBe('Newer announcement →');
});

<?php

use App\Models\Artist;
use App\Models\Blog;
use App\Models\Photo;
use App\Models\Playlist;
use App\Models\Release;
use App\Support\NewsItem;
use App\Support\Seo\PublicPage;
use App\Support\Seo\PublicPages;
use Dom\Element;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    fakeSpotifyThumbnails();
});

/**
 * The Play buttons matching the selector: what they load and what they read out.
 *
 * @return list<array{uri: ?string, title: ?string, credit: ?string, url: ?string, pressed: ?string, text: string}>
 */
function playButtons(TestResponse|string $html, string $selector = 'main [data-play]'): array
{
    return array_map(fn (Element $button): array => [
        'uri' => $button->getAttribute('data-play'),
        'title' => $button->getAttribute('data-play-title'),
        'credit' => $button->getAttribute('data-play-credit'),
        'url' => $button->getAttribute('data-play-url'),
        'pressed' => $button->getAttribute('aria-pressed'),
        'text' => trim(preg_replace('/\s+/', ' ', $button->textContent)),
    ], iterator_to_array(htmlDocument($html)->querySelectorAll($selector), false));
}

/**
 * A release by a roster artist, playable on Spotify.
 */
function playableRelease(string $title, string $spotifyUrl, array $attributes = []): Release
{
    $release = Release::factory()->create(['title' => $title, 'spotify_url' => $spotifyUrl, ...$attributes]);
    $release->artists()->attach(Artist::factory()->create(['name' => 'Snow N Stuff']));

    return $release;
}

it('puts a Play button beside each release title, outside the cover link', function () {
    playableRelease('Speak To Me', 'https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N', ['released_at' => '2025-04-04']);
    Release::factory()->create(['title' => 'Short Link', 'released_at' => '2025-03-01', 'spotify_url' => 'https://spotify.link/aBcD3fGh1j']);
    Release::factory()->create(['title' => 'Smartlink Only', 'released_at' => '2025-02-01', 'spotify_url' => null, 'smartlink_url' => 'https://ditto.fm/smartlink-only']);

    $response = $this->get('/releases')->assertOk();

    expect(playButtons($response))->toBe([[
        'uri' => 'spotify:album:3zifCl5R2DaZGEmrPNUM1N',
        'title' => 'Speak To Me',
        'credit' => 'Snow N Stuff',
        'url' => route('releases.show', 'speak-to-me'),
        'pressed' => 'false',
        'text' => 'Play Speak To Me by Snow N Stuff',
    ]])
        ->and(htmlDocument($response)->querySelectorAll('a [data-play], [data-play] a'))->toHaveCount(0);
    $response->assertSeeText('Short Link')->assertSeeText('Smartlink Only');
});

it('plays a playlist from its card', function () {
    Playlist::factory()->create(['title' => 'Vocal Deep House 2026', 'order' => 1, 'spotify_url' => 'https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO']);
    Playlist::factory()->create(['title' => 'Shared As A Short Link', 'order' => 2, 'spotify_url' => 'https://spotify.link/aBcD3fGh1j']);

    expect(playButtons($this->get('/playlists')->assertOk()))->toBe([[
        'uri' => 'spotify:playlist:28I7hCUFTyqblhgu5yGkOO',
        'title' => 'Vocal Deep House 2026',
        'credit' => null,
        'url' => route('playlists.index'),
        'pressed' => 'false',
        'text' => 'Play Vocal Deep House 2026',
    ]]);
});

it('offers Play on a news entry for a release, never for a post', function () {
    $release = playableRelease('I Know', 'https://open.spotify.com/track/4ZyLmBV36fgNyo3IAM8xc3');
    $post = Blog::factory()->published()->create();

    $releaseCard = (string) $this->blade('<x-news-card :item="$item" />', ['item' => NewsItem::fromRelease($release->load('artists'))]);
    $postCard = (string) $this->blade('<x-news-card :item="$item" />', ['item' => NewsItem::fromPost($post)]);

    expect(playButtons($releaseCard, '[data-play]'))->toBe([[
        'uri' => 'spotify:track:4ZyLmBV36fgNyo3IAM8xc3',
        'title' => 'I Know',
        'credit' => 'Snow N Stuff',
        'url' => route('releases.show', 'i-know'),
        'pressed' => 'false',
        'text' => 'Play I Know by Snow N Stuff',
    ]])
        ->and(playButtons($postCard, '[data-play]'))->toBe([]);
});

it('has no Play button on the news pages, which list posts only', function () {
    playableRelease('I Know', 'https://open.spotify.com/track/4ZyLmBV36fgNyo3IAM8xc3', ['released_at' => now()->subWeek()]);
    Blog::factory()->published()->count(2)->create();
    $post = Blog::factory()->published()->create(['slug' => 'i-know-is-out']);

    expect(playButtons($this->get('/blog')->assertOk()))->toBe([])
        ->and(playButtons($this->get("/blog/{$post->slug}")->assertOk()))->toBe([]);
});

it('leads the homepage hero with Play and marks its record for the spin', function () {
    playableRelease('Human Made', 'https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr', ['is_featured' => true]);

    $response = $this->get('/')->assertOk();
    $hero = 'section[aria-labelledby="hero-title"]';

    expect(playButtons($response, "{$hero} [data-play]"))->toBe([[
        'uri' => 'spotify:track:5LoRtT4HMphu4n2OyJn4Cr',
        'title' => 'Human Made',
        'credit' => 'Snow N Stuff',
        'url' => route('releases.show', 'human-made'),
        'pressed' => 'false',
        'text' => 'Play Human Made by Snow N Stuff',
    ]])
        ->and(htmlDocument($response)->querySelector("{$hero} [data-play-vinyl]")?->getAttribute('data-play-vinyl'))->toBe('spotify:track:5LoRtT4HMphu4n2OyJn4Cr');
    $response->assertSeeInOrder(['Play<span class="sr-only"> Human Made by Snow N Stuff', 'Listen now'], escape: false);
});

it('keeps Listen now as the only way to listen when Spotify cannot play the release', function () {
    Release::factory()->featured()->create([
        'title' => 'Pre-save Me',
        'spotify_url' => 'https://spotify.link/aBcD3fGh1j',
        'smartlink_url' => 'https://ditto.fm/pre-save-me',
    ]);

    $response = $this->get('/')->assertOk();

    expect(playButtons($response))->toBe([])
        ->and(htmlDocument($response)->querySelectorAll('[data-play-vinyl]'))->toHaveCount(0);
    $response->assertSeeText('Listen now');

    $this->get('/releases/pre-save-me')->assertOk()->assertDontSee('data-play=', escape: false)->assertSeeText('Listen now');
});

it('puts Play in the release page hero', function () {
    playableRelease('Speak To Me', 'https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N');

    expect(playButtons($this->get('/releases/speak-to-me')->assertOk(), 'main > section:first-child [data-play]'))->toBe([[
        'uri' => 'spotify:album:3zifCl5R2DaZGEmrPNUM1N',
        'title' => 'Speak To Me',
        'credit' => 'Snow N Stuff',
        'url' => route('releases.show', 'speak-to-me'),
        'pressed' => 'false',
        'text' => 'Play Speak To Me by Snow N Stuff',
    ]]);
});

it('plays the newest release Spotify can play from the artist page', function () {
    $artist = Artist::factory()->create(['name' => 'G&S']);
    $artist->releases()->attach([
        Release::factory()->create(['title' => 'Newest', 'released_at' => '2026-01-01', 'spotify_url' => 'https://spotify.link/aBcD3fGh1j'])->id,
        Release::factory()->create(['title' => 'Back to Black', 'released_at' => '2025-01-01', 'spotify_url' => 'https://open.spotify.com/track/6xl7BbDBYIkWXcys8Shu4H'])->id,
        Release::factory()->create(['title' => 'Oldest', 'released_at' => '2024-01-01', 'spotify_url' => 'https://open.spotify.com/track/51R9KqdvmKMPUkReMs7aH3'])->id,
    ]);

    expect(playButtons($this->get('/artists/g-and-s')->assertOk(), 'main > section:first-child [data-play]'))->toBe([[
        'uri' => 'spotify:track:6xl7BbDBYIkWXcys8Shu4H',
        'title' => 'Back to Black',
        'credit' => 'G&S',
        'url' => route('releases.show', 'back-to-black'),
        'pressed' => 'false',
        'text' => 'Play latest: Back to Black by G&S',
    ]]);
});

it('has no Play latest button when Spotify can play none of the artist releases', function () {
    $artist = Artist::factory()->create(['name' => 'G&S']);
    $artist->releases()->attach(Release::factory()->create(['spotify_url' => 'https://spotify.link/aBcD3fGh1j']));

    $this->get('/artists/g-and-s')->assertOk()->assertDontSeeText('Play latest');
});

it('never renders the footer player, which the browser creates on the first Play', function () {
    Artist::factory()->create(['name' => 'G&S']);
    playableRelease('Back to Black', 'https://open.spotify.com/track/6xl7BbDBYIkWXcys8Shu4H', ['is_featured' => true]);
    Playlist::factory()->create();
    Photo::factory()->create();
    Blog::factory()->published()->create([
        'slug' => 'back-to-black-is-out',
        'content' => '<p>Out now.</p><p><iframe src="https://open.spotify.com/embed/track/6xl7BbDBYIkWXcys8Shu4H" width="100%" height="352"></iframe></p>',
    ]);
    $paths = app(PublicPages::class)->all()->map(fn (PublicPage $page): string => $page->path)->push('/releases/nope');

    foreach ($paths as $path) {
        $response = $this->get($path);

        expect(htmlDocument($response)->querySelectorAll('[data-sns-player]'))->toHaveCount(0, "{$path} renders the player")
            ->and($response->getContent())->not->toContain('open.spotify.com/embed/iframe-api');
    }

    // A post keeps its own embedded players, as placeholders loaded on consent or on a click.
    $this->get('/blog/back-to-black-is-out')->assertSee('data-embed-src="https://open.spotify.com/embed/track/6xl7BbDBYIkWXcys8Shu4H"', escape: false);
});

it('leaves the Play buttons out of the Markdown version', function (string $path, string $markdownPath, string $content) {
    $artist = Artist::factory()->create(['name' => 'G&S']);
    playableRelease('Back to Black', 'https://open.spotify.com/track/6xl7BbDBYIkWXcys8Shu4H', ['is_featured' => true])->artists()->attach($artist);
    Playlist::factory()->create(['title' => 'Ibiza 2026']);

    $html = $this->get($path)->assertOk();
    $markdown = $this->get($markdownPath)->assertOk()->getContent();

    expect(playButtons($html))->not->toBe([])
        ->and($markdown)->toContain($content)
        ->not->toMatch('/\bPlay\b/');
})->with([
    'homepage' => ['/', '/index.md', 'Back to Black'],
    'catalogue' => ['/releases', '/releases.md', 'Back to Black'],
    'release' => ['/releases/back-to-black', '/releases/back-to-black.md', 'Back to Black'],
    'artist' => ['/artists/g-and-s', '/artists/g-and-s.md', 'Back to Black'],
    'playlists' => ['/playlists', '/playlists.md', 'Ibiza 2026'],
]);

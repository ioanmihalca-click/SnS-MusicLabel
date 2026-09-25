<?php

use App\Models\Artist;
use App\Models\Blog;
use App\Models\Photo;
use App\Models\Playlist;
use App\Models\Release;
use App\Support\Seo\PublicPage;
use App\Support\Seo\PublicPages;
use Dom\HTMLDocument;
use Illuminate\Testing\TestResponse;

/**
 * How many elements match the selector in the response's HTML.
 */
function countElements(TestResponse $response, string $selector): int
{
    return HTMLDocument::createFromString($response->getContent(), LIBXML_NOERROR)->querySelectorAll($selector)->length;
}

it('gives every public page one <h1>, one <main> and one valid JSON-LD graph', function () {
    fakeSpotifyThumbnails();
    $artist = Artist::factory()->create(['name' => 'G&S']);
    Artist::factory()->legacy()->create(['name' => 'Style da Kid']);
    Release::factory()->withTracks()->create(['title' => 'Back to Black'])->artists()->attach($artist);
    Release::factory()->legacy()->create(['title' => 'Warrior', 'artist_display' => 'THK & Pacha Man']);
    Playlist::factory()->create();
    Playlist::factory()->legacy()->create();
    Photo::factory()->create();
    Blog::factory()->published()->create(['title' => 'Back to Black Is Out', 'slug' => 'back-to-black-is-out']);

    $pages = app(PublicPages::class)->all();

    expect($pages->map(fn (PublicPage $page): string => $page->path)->all())->toContain(
        '/', '/about', '/playlists', '/releases', '/releases/back-to-black', '/releases/warrior',
        '/artists', '/artists/g-and-s', '/artists/style-da-kid', '/blog', '/blog/back-to-black-is-out',
    );

    foreach ($pages as $page) {
        $response = $this->get($page->path)->assertOk();

        expect(countElements($response, 'h1'))->toBe(1, "{$page->path} should have exactly one <h1>")
            ->and(countElements($response, 'main'))->toBe(1, "{$page->path} should have exactly one <main>")
            ->and(jsonLd($response)['@context'])->toBe('https://schema.org');
    }
});

it('gives the redesigned pages a header with the main navigation and a footer', function (string $uri) {
    fakeSpotifyThumbnails();
    Release::factory()->create(['title' => 'Back to Black']);
    Artist::factory()->create(['name' => 'G&S']);

    $response = $this->get($uri);

    expect(countElements($response, 'body > header nav[aria-label="Main"]'))->toBe(1)
        ->and(countElements($response, 'body > main'))->toBe(1)
        ->and(countElements($response, 'body > footer'))->toBe(1)
        ->and(countElements($response, 'h1'))->toBe(1);
})->with([
    '/releases',
    '/releases/back-to-black',
    '/artists',
    '/artists/g-and-s',
    '/playlists',
    '/about',
    'not found' => '/releases/nope',
]);

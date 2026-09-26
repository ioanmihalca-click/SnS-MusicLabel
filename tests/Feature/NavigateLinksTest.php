<?php

use App\Models\Artist;
use App\Models\Blog;
use App\Models\Photo;
use App\Models\Playlist;
use App\Models\Release;
use App\Support\Seo\PublicPage;
use App\Support\Seo\PublicPages;
use Dom\Element;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    fakeSpotifyThumbnails();
});

/**
 * The links of the page's header, main content and footer.
 *
 * @return list<Element>
 */
function siteLinks(TestResponse $response, string $selector = 'body > header a[href], body > main a[href], body > footer a[href]'): array
{
    return iterator_to_array(htmlDocument($response)->querySelectorAll($selector), false);
}

/**
 * Whether the link should load with wire:navigate, which keeps the footer
 * player playing: a page of this site opened in the same tab. Files, text and
 * XML routes, the admin, the lightbox, links inside a post's body and
 * Livewire's pagination load as before.
 */
function loadsWithoutReload(Element $link): bool
{
    $url = parse_url((string) $link->getAttribute('href'));
    $path = $url['path'] ?? '';
    $isThisSite = ! isset($url['scheme']) && ! isset($url['host']) && $path !== ''
        || in_array($url['scheme'] ?? null, ['http', 'https'], true) && ($url['host'] ?? null) === parse_url(url('/'), PHP_URL_HOST);

    return $isThisSite
        && $link->getAttribute('target') !== '_blank'
        && ! $link->hasAttribute('data-fancybox')
        && ! $link->hasAttribute('wire:click.prevent')
        && $link->closest('.article-body') === null
        && ! preg_match('#^/(admin|storage)(/|$)|\.(md|txt|xml)$#', $path);
}

/**
 * A catalogue with every kind of link: external profiles, a smartlink, a
 * press kit, photos in the lightbox, posts with links in their body and
 * enough posts for a second page.
 */
function seedEveryKindOfLink(): void
{
    $artist = Artist::factory()->create([
        'name' => 'G&S',
        'instagram_url' => 'https://www.instagram.com/gands',
        'press_kit' => 'press-kits/g-and-s.pdf',
    ]);
    Release::factory()->withTracks()->create([
        'title' => 'Back to Black',
        'is_featured' => true,
        'smartlink_url' => 'https://ditto.fm/back-to-black',
    ])->artists()->attach($artist);
    Release::factory()->create(['title' => 'Warrior', 'artist_display' => 'G&S & Pacha Man'])->artists()->attach($artist);
    Playlist::factory()->create();
    Photo::factory()->count(2)->create();
    Blog::factory()->published()->count(10)->create();
    Blog::factory()->published()->create([
        'slug' => 'back-to-black-is-out',
        'content' => '<p>Listen to <a href="/releases/back-to-black">Back to Black</a> or read <a href="https://example.com/review">the review</a>.</p>',
    ]);
}

it('loads every link to a page of the site with wire:navigate, and nothing else', function () {
    seedEveryKindOfLink();
    $paths = app(PublicPages::class)->all()->map(fn (PublicPage $page): string => $page->path)->push('/releases/nope');
    $mismatches = [];

    foreach ($paths as $path) {
        foreach (siteLinks($this->get($path)) as $link) {
            if ($link->hasAttribute('wire:navigate') !== loadsWithoutReload($link)) {
                $mismatches[] = "{$path}: ".$link->getAttribute('href');
            }
        }
    }

    expect($mismatches)->toBe([]);
});

it('keeps wire:navigate off e-mail, external, press kit and lightbox links', function () {
    seedEveryKindOfLink();

    $aboutLinks = siteLinks($this->get('/about'), 'main a[href^="mailto:"], main a[target="_blank"], main [data-fancybox]');
    $pressKitLinks = siteLinks($this->get('/artists/g-and-s'), 'main a[href$="/storage/press-kits/g-and-s.pdf"]');

    expect($aboutLinks)->toHaveCount(4 + 4 + 2)
        ->and($pressKitLinks)->toHaveCount(1)
        ->and(array_filter([...$aboutLinks, ...$pressKitLinks], fn (Element $link): bool => $link->hasAttribute('wire:navigate')))->toBe([]);
});

it('keeps wire:navigate off the links in a post and off the pagination', function () {
    seedEveryKindOfLink();

    $postLinks = siteLinks($this->get('/blog/back-to-black-is-out'), '.article-body a');
    $paginationLinks = siteLinks($this->get('/blog'), 'nav[aria-label="Pagination"] a');

    expect($postLinks)->toHaveCount(2)
        ->and($paginationLinks)->not->toBeEmpty()
        ->and(array_filter([...$postLinks, ...$paginationLinks], fn (Element $link): bool => $link->hasAttribute('wire:navigate')))->toBe([]);
});

it('loads an internal button with wire:navigate and opens an external one in a new tab', function () {
    $internal = $this->blade('<x-site.button :href="route(\'releases.index\')">Browse releases</x-site.button>');
    $external = $this->blade('<x-site.button href="https://ditto.fm/back-to-black" external>Listen now</x-site.button>');

    $internal->assertSee('wire:navigate', escape: false)->assertDontSee('target="_blank"', escape: false);
    $external->assertSee('target="_blank" rel="noopener"', escape: false)->assertDontSee('wire:navigate', escape: false);
});

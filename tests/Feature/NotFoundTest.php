<?php

it('shows missing pages in the new layout, kept out of the index', function (string $uri) {
    $response = $this->get($uri);

    $response->assertNotFound()
        ->assertSee('<html lang="en" class="theme-site bg-ink">', escape: false)
        ->assertSee('<meta name="robots" content="noindex, follow">', escape: false)
        ->assertDontSee('rel="canonical"', escape: false)
        ->assertDontSee('type="text/markdown"', escape: false)
        ->assertSeeText('Page not found')
        ->assertSee('<nav aria-label="Main"', escape: false)
        ->assertSeeText('Web application by Click Studios Digital');

    expect(jsonLd($response)['@graph'])->not->toBeEmpty();
})->with([
    'unknown release' => '/releases/nope',
    'unknown artist' => '/artists/nobody',
    'unknown route' => '/no-such-page',
]);

it('does not turn a missing page into Markdown', function () {
    $this->get('/releases/nope.md')
        ->assertNotFound()
        ->assertHeader('Content-Type', 'text/html; charset=utf-8');
});

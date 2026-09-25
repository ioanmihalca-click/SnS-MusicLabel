<?php

use App\Models\Artist;
use App\Models\Blog;

it('serves the homepage as Markdown to clients that accept it', function (string $uri, array $headers) {
    $response = $this->get($uri, $headers);

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
        ->assertHeader('X-Robots-Tag', 'noindex')
        ->assertHeader('Content-Signal', 'search=yes, ai-input=yes, ai-train=no');
    expect($response->getContent())
        ->toContain("# Snow 'n' Stuff")
        ->toContain('glenn@1namm.com')
        ->not->toContain('<')
        ->not->toContain('/#artists')
        ->not->toContain('Quick Links')
        ->not->toContain('All rights reserved');
})->with([
    'Accept header' => ['/', ['Accept' => 'text/markdown']],
    '.md suffix' => ['/index.md', []],
    'agent reading for a user' => ['/', ['User-Agent' => 'Mozilla/5.0 (compatible; Claude-User/1.0; +Claude-User@anthropic.com)']],
]);

it('keeps serving HTML to crawlers', function (string $userAgent) {
    $this->get('/', ['User-Agent' => $userAgent])
        ->assertOk()
        ->assertHeader('Content-Type', 'text/html; charset=utf-8');
})->with([
    'Googlebot' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
    'GPTBot' => 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.2; +https://openai.com/gptbot)',
]);

it('redirects /index to the homepage unless the Markdown version is requested', function () {
    $this->get('/index')->assertMovedPermanently()->assertRedirect('/');
});

it('keeps a blog post title and text in its Markdown version', function () {
    $post = Blog::factory()->published()->create([
        'title' => 'Speak To Me Is Out',
        'content' => '<p>Our new single is <strong>out now</strong>.</p>',
    ]);

    $markdown = $this->get("/blog/{$post->slug}.md")
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
        ->getContent();

    expect($markdown)
        ->toContain('# Speak To Me Is Out')
        ->toContain('Our new single is **out now**.')
        ->not->toContain('Quick Links');
});

it('reflects an edited post straight away', function () {
    $post = Blog::factory()->published()->create(['title' => 'First Title']);
    $this->get("/blog/{$post->slug}.md")->assertSee('First Title');

    $post->update(['title' => 'Second Title']);

    $this->get("/blog/{$post->slug}.md")->assertSee('Second Title');
});

it('returns 404 for the Markdown version of a missing page', function () {
    $this->get('/blog/nonexistent-slug.md')->assertNotFound();
});

it('decodes HTML entities, so "G&S" does not read "G&amp;S"', function () {
    fakeSpotifyThumbnails();
    Artist::factory()->create(['name' => 'G&S', 'description' => '<p>G&amp;S is back with Back to Black.</p>']);

    $markdown = $this->get('/index.md')->assertOk()->getContent();

    expect($markdown)
        ->toContain('THK · G&S')
        ->toContain('G&S is back with Back to Black.')
        ->toContain('Artists and Repertoire (A&R)')
        ->not->toContain('&amp;')
        ->not->toContain('&#039;');
});

it('writes definition lists and links without stray whitespace', function () {
    $markdown = $this->get('/about.md')->assertOk()->getContent();

    expect($markdown)
        ->toContain('Demo: demo@1namm.com')
        ->toContain('[LinkedIn ↗](https://www.linkedin.com/in/glenn-forrestgate-457228a9)')
        ->toContain("# About\n");
});

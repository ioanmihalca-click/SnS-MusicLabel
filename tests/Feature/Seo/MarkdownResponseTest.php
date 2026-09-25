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
        ->toStartWith("# Snow 'n' Stuff\n")
        ->and(substr_count($response->getContent(), "# Snow 'n' Stuff"))->toBe(1)
        ->and($response->getContent())
        ->toContain('glenn@1namm.com', 'info@1namm.com', 'demo@1namm.com')
        ->not->toContain('<')
        ->not->toContain('All rights reserved')
        ->not->toContain('Web application by Click Studios Digital');
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

    expect($this->get('/artists/g-and-s.md')->assertOk()->getContent())
        ->toContain('# G&S')
        ->toContain('G&S is back with Back to Black.')
        ->not->toContain('&amp;');

    expect($this->get('/index.md')->assertOk()->getContent())
        ->toContain("Management for THK · G&S · Snow 'n' Stuff · Style da Kid")
        ->toContain('A&R')
        ->not->toContain('&amp;')
        ->not->toContain('&#039;');
});

it('keeps the players of a post as links in its Markdown version', function () {
    $post = Blog::factory()->published()->create([
        'title' => 'Speak To Me Is Out',
        'content' => '<p>Out now.</p><p><iframe style="border-radius: 12px;" src="https://open.spotify.com/embed/album/3zifCl5R2DaZGEmrPNUM1N?utm_source=generator" width="100%" height="352"></iframe></p>'
            .'<p><iframe src="https://embed.beatport.com/?id=20186693&amp;type=track" width="100%" height="162"></iframe></p>'
            .'<p>Written by Glenn Forrestgate</p>',
    ]);

    expect($this->get("/blog/{$post->slug}.md")->assertOk()->getContent())
        ->toContain("Out now.\n\n[Listen on Spotify](https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N)\n")
        ->toContain("\nWritten by Glenn Forrestgate")
        ->toContain('[Listen on Beatport](https://embed.beatport.com/?id=20186693&type=track)')
        ->not->toContain('<');
});

it('writes definition lists and links without stray whitespace', function () {
    $markdown = $this->get('/about.md')->assertOk()->getContent();

    expect($markdown)
        ->toContain('Demo: demo@1namm.com')
        ->toContain('[LinkedIn ↗](https://www.linkedin.com/in/glenn-forrestgate-457228a9)')
        ->toContain("# About\n");
});

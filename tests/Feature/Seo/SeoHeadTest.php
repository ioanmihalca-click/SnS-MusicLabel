<?php

use App\Models\Blog;

it('prints the canonical, social cards and Markdown alternate of the homepage', function () {
    config(['app.url' => 'https://snow-n-stuff.com']);

    $this->get('/')
        ->assertOk()
        ->assertSee('<title>Snow &#039;n&#039; Stuff - Music Management, Label and Music Production</title>', escape: false)
        ->assertSee('<link rel="canonical" href="https://snow-n-stuff.com/">', escape: false)
        ->assertSee('<link rel="alternate" type="text/markdown" href="https://snow-n-stuff.com/index.md">', escape: false)
        ->assertSee('<link rel="describedby" href="https://snow-n-stuff.com/llms.txt" type="text/plain">', escape: false)
        ->assertSee('<meta property="og:url" content="https://snow-n-stuff.com/">', escape: false)
        ->assertSee('<meta property="og:locale" content="en_US">', escape: false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', escape: false)
        ->assertDontSee('name="robots"', escape: false);
});

it('builds the canonical from the app URL, whatever the request host and query', function () {
    config(['app.url' => 'https://snow-n-stuff.com']);

    $this->get('http://www.snow-n-stuff.com/blog?page=2')
        ->assertOk()
        ->assertSee('<link rel="canonical" href="https://snow-n-stuff.com/blog">', escape: false)
        ->assertSee('<link rel="alternate" type="text/markdown" href="https://snow-n-stuff.com/blog.md">', escape: false);
});

it('keeps blog search results out of the index', function () {
    $this->get('/blog?search=house')
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex, follow">', escape: false)
        ->assertDontSee('rel="canonical"', escape: false);
});

it('describes the label once, with a stable id, in a single JSON-LD graph', function () {
    config(['app.url' => 'https://snow-n-stuff.com']);

    $graph = collect(jsonLd($this->get('/'))['@graph']);
    $organization = $graph->firstWhere('@type', 'Organization');

    expect($graph->where('@type', 'Organization'))->toHaveCount(1)
        ->and($organization['@id'])->toBe('https://snow-n-stuff.com/#organization')
        ->and($organization['logo']['url'])->toEndWith('/assets/favicon/web-app-manifest-512x512.png')
        ->and(collect($organization['founder'])->pluck('name')->all())->toBe(['Glenn Forrestgate', 'Style da Kid'])
        ->and($organization['sameAs'])->toBe([
            'https://www.instagram.com/snow_n_stuff',
            'https://www.facebook.com/SnowNStuff',
            'https://x.com/G_n_S_',
        ])
        ->and($organization)->not->toHaveKeys(['alternateName', 'genre', 'founders'])
        ->and($graph->firstWhere('@type', 'WebSite')['publisher'])->toBe(['@id' => 'https://snow-n-stuff.com/#organization']);
});

it('marks up a blog post as an article published by the label', function () {
    config(['app.url' => 'https://snow-n-stuff.com']);
    $post = Blog::factory()->published()->create([
        'title' => 'Speak To Me Is Out',
        'slug' => 'speak-to-me-is-out',
        'meta_title' => null,
        'meta_description' => 'Our new single is out now.',
        'cover_image' => 'blog-covers/speak-to-me.jpg',
    ]);

    $response = $this->get('/blog/speak-to-me-is-out');

    $response->assertOk()
        ->assertSee('<title>Speak To Me Is Out</title>', escape: false)
        ->assertSee('<meta name="description" content="Our new single is out now.">', escape: false)
        ->assertSee('<meta property="og:type" content="article">', escape: false)
        ->assertSee('<meta property="og:image" content="'.$post->coverUrl().'">', escape: false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', escape: false);

    $graph = collect(jsonLd($response)['@graph']);
    $article = $graph->firstWhere('@type', 'BlogPosting');

    expect($article['headline'])->toBe('Speak To Me Is Out')
        ->and($article['url'])->toBe('https://snow-n-stuff.com/blog/speak-to-me-is-out')
        ->and($article['image'])->toBe($post->coverUrl())
        ->and($article['author'])->toBe(['@id' => 'https://snow-n-stuff.com/#organization'])
        ->and($article['publisher'])->toBe(['@id' => 'https://snow-n-stuff.com/#organization'])
        ->and(collect($graph->firstWhere('@type', 'BreadcrumbList')['itemListElement'])->pluck('item')->all())->toBe([
            'https://snow-n-stuff.com/',
            'https://snow-n-stuff.com/blog',
            'https://snow-n-stuff.com/blog/speak-to-me-is-out',
        ]);
});

it('keeps HTML in content from closing the JSON-LD script', function () {
    $post = Blog::factory()->published()->create(['title' => '</script><script>alert(1)</script>']);

    $response = $this->get("/blog/{$post->slug}");

    expect(collect(jsonLd($response)['@graph'])->firstWhere('@type', 'BlogPosting')['headline'])
        ->toBe('</script><script>alert(1)</script>');
    $response->assertDontSee('<script>alert(1)</script>', escape: false);
});

it('prints the search engine verification codes when configured', function () {
    config([
        'services.google.site_verification' => 'google-code',
        'services.bing.site_verification' => 'bing-code',
    ]);

    $this->get('/')
        ->assertSee('<meta name="google-site-verification" content="google-code">', escape: false)
        ->assertSee('<meta name="msvalidate.01" content="bing-code">', escape: false);
});

it('never prints Google Analytics, and gives its id to the consent script in production only', function () {
    config(['services.google_analytics.id' => 'G-TEST123']);

    $this->get('/')
        ->assertDontSee('googletagmanager.com', escape: false)
        ->assertDontSee('gtag(', escape: false)
        ->assertDontSee('name="sns-ga-id"', escape: false);

    app()->detectEnvironment(fn (): string => 'production');

    $this->get('/')
        ->assertDontSee('googletagmanager.com', escape: false)
        ->assertDontSee('gtag(', escape: false)
        ->assertSee('<meta name="sns-ga-id" content="G-TEST123">', escape: false);
});

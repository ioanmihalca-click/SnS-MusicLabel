<?php

it('closes the whole site to crawlers outside production', function () {
    $this->get('/robots.txt')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertContent("User-agent: *\nDisallow: /\n");
});

it('opens the site to search engines and user agents but not to training crawlers in production', function () {
    app()->detectEnvironment(fn (): string => 'production');
    config(['app.url' => 'https://snow-n-stuff.com']);

    $this->get('/robots.txt')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertContent(<<<'TXT'
            User-agent: Googlebot
            User-agent: Bingbot
            User-agent: OAI-SearchBot
            User-agent: ChatGPT-User
            User-agent: Claude-SearchBot
            User-agent: Claude-User
            User-agent: PerplexityBot
            User-agent: Perplexity-User
            User-agent: Applebot
            Disallow: /admin

            User-agent: GPTBot
            User-agent: ClaudeBot
            User-agent: Google-Extended
            User-agent: Applebot-Extended
            User-agent: CCBot
            User-agent: meta-externalagent
            User-agent: Bytespider
            Disallow: /

            User-agent: *
            Disallow: /admin
            Content-Signal: search=yes, ai-input=yes, ai-train=no

            Sitemap: https://snow-n-stuff.com/sitemap.xml

            TXT);
});

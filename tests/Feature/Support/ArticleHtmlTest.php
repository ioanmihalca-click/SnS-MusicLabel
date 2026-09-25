<?php

use App\Support\ArticleHtml;

it('drops pasted styles, classes, ids and handlers but keeps the text formatting', function () {
    $html = ArticleHtml::render(
        '<p class="p1" id="x" style="margin: 0px; font-size: 21px;" data-start="0" onclick="alert(1)">'
        .'<span style="font-size: 14pt;">Our <strong data-end="16">new single</strong> is <em>out</em>.</span></p>'
        .'<p><font color="red">Big</font> <a style="color: #ba372a;" href="https://www.beatport.com/track/vision/19714313">news</a></p>'
    );

    expect($html)->toBe(
        '<p>Our <strong>new single</strong> is <em>out</em>.</p>'
        .'<p>Big <a href="https://www.beatport.com/track/vision/19714313">news</a></p>'
    );
});

it('removes empty paragraphs and trailing line breaks', function () {
    $html = ArticleHtml::render('<p>One<br /><br /></p><p>&nbsp;</p><p class="p1"><strong>&nbsp;</strong></p><p><span id="docs-internal-guid"></span></p><p>Two</p>');

    expect($html)->toBe('<p>One</p><p>Two</p>');
});

it('keeps a single <h1>, the post title, by turning headings in the text into <h2>', function () {
    expect(ArticleHtml::render('<h1 style="color: red;">Heading</h1><h3>Sub</h3>'))->toBe('<h2>Heading</h2><h3>Sub</h3>');
});

it('removes scripts and unsafe links', function () {
    $html = ArticleHtml::render(
        '<p>Hi<script>alert(1)</script></p>'
        .'<p><a href="javascript:alert(1)">bad</a> <a href=" java&#09;script:alert(1)">tab</a> <a href="/releases">ok</a></p>'
        .'<p><img src="javascript:alert(1)"><img src="https://example.com/cover.jpg" onerror="alert(1)" alt="Cover"></p>'
    );

    expect($html)
        ->toContain('<p>Hi</p>')
        ->toContain('bad tab <a href="/releases">ok</a>')
        ->toContain('<img src="https://example.com/cover.jpg" alt="Cover" loading="lazy" decoding="async">')
        ->not->toContain('javascript')
        ->not->toContain('script>')
        ->not->toContain('onerror');
});

it('opens links in a new tab safely', function () {
    expect(ArticleHtml::render('<p><a href="https://bfan.link/nubian-heat" target="_blank">Pre-order</a> <a href="/blog" target="_self">Blog</a></p>'))
        ->toBe('<p><a href="https://bfan.link/nubian-heat" target="_blank" rel="noopener">Pre-order</a> <a href="/blog">Blog</a></p>');
});

it('keeps the players from Spotify, Beatport and nfan.link with a link to the music', function (string $iframe, string $src, string $link) {
    $html = ArticleHtml::render('<p>Out now.</p><p>'.$iframe.'</p>');

    expect($html)
        ->toStartWith('<p>Out now.</p><figure class="article-embed"><iframe src="'.$src.'"')
        ->toContain('loading="lazy"')
        ->toContain('title="')
        ->toContain('<figcaption><a href="'.$link.'" target="_blank" rel="noopener">')
        ->not->toContain('style=')
        ->not->toContain('<p><figure');
})->with([
    'Spotify album' => [
        '<iframe style="border-radius: 12px;" src="https://open.spotify.com/embed/album/3zifCl5R2DaZGEmrPNUM1N?utm_source=generator" width="100%" height="352" frameborder="0" allowfullscreen="allowfullscreen" loading="lazy"></iframe>',
        'https://open.spotify.com/embed/album/3zifCl5R2DaZGEmrPNUM1N',
        'https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N',
    ],
    'Beatport track' => [
        '<iframe style="max-width: 600px;" src="https://embed.beatport.com/?id=20186693&amp;type=track" width="100%" height="162" frameborder="0" scrolling="no"></iframe>',
        'https://embed.beatport.com/?id=20186693&amp;type=track',
        'https://embed.beatport.com/?id=20186693&amp;type=track',
    ],
    'nfan.link smartlink' => [
        '<iframe style="border: 3px solid #ffffff;" src="https://nfan.link/snow-n-stuff" width="600" height="400" scrolling="no"></iframe>',
        'https://nfan.link/snow-n-stuff',
        'https://nfan.link/snow-n-stuff',
    ],
]);

it('captions the players so their Markdown keeps the music', function () {
    $html = ArticleHtml::render('<iframe src="https://open.spotify.com/embed/track/0Co9icwI2q3fAKlJUXu8Si?utm_source=generator" height="352"></iframe>');

    expect($html)->toContain('>Listen on Spotify</a></figcaption>');
});

it('drops players from any other site', function (string $src) {
    expect(ArticleHtml::render('<p>Text</p><p><iframe src="'.$src.'"></iframe></p>'))->toBe('<p>Text</p>');
})->with([
    'YouTube' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
    'lookalike host' => 'https://open.spotify.com.evil.example/embed/track/0Co9icwI2q3fAKlJUXu8Si',
    'subdomain' => 'https://evil.nfan.link/x',
    'javascript' => 'javascript:alert(1)',
]);

it('moves players out of the paragraph that held them, in order', function () {
    $html = ArticleHtml::render(
        '<p><em> <iframe src="https://open.spotify.com/embed/track/2B0xsnWUjm7cPLs9gGoepp"></iframe> '
        .'<iframe src="https://open.spotify.com/embed/track/4Wfne1wWnOCvOhl8x1P8fp"></iframe> </em></p><p>After</p>'
    );

    expect($html)->toMatch('#^<figure class="article-embed"><iframe src="https://open\.spotify\.com/embed/track/2B0xsnWUjm7cPLs9gGoepp".*?</figure><figure class="article-embed"><iframe src="https://open\.spotify\.com/embed/track/4Wfne1wWnOCvOhl8x1P8fp".*?</figure><p>After</p>$#');
});

it('turns a Spotify link alone in a paragraph into a player', function (string $paragraph, string $caption) {
    $html = ArticleHtml::render('<p>Listen:</p>'.$paragraph);

    expect($html)->toBe(
        '<p>Listen:</p><figure class="article-embed">'
        .'<iframe src="https://open.spotify.com/embed/playlist/28I7hCUFTyqblhgu5yGkOO" title="Spotify player" height="352" loading="lazy" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"></iframe>'
        .'<figcaption><a href="https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO" target="_blank" rel="noopener">'.$caption.'</a></figcaption></figure>'
    );
})->with([
    'bare URL' => ['<p>https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO?si=5779cb3c0a6b41c6</p>', 'Listen on Spotify'],
    'link showing its URL' => ['<p><a href="https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO">https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO</a></p>', 'Listen on Spotify'],
    'link with text, in bold' => ['<p><strong><a href="https://open.spotify.com/intl-de/playlist/28I7hCUFTyqblhgu5yGkOO?si=x">Follow the playlist</a></strong></p>', 'Follow the playlist'],
]);

it('leaves Spotify links inside a sentence, or already embedded, as links', function () {
    $sentence = '<p>Stream it <a href="https://open.spotify.com/track/0Co9icwI2q3fAKlJUXu8Si">on Spotify</a> today.</p>';
    $embedded = '<p><a href="https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO">Follow</a></p><p><iframe src="https://open.spotify.com/embed/playlist/28I7hCUFTyqblhgu5yGkOO"></iframe></p>';

    expect(ArticleHtml::render($sentence))->toBe($sentence)
        ->and(substr_count(ArticleHtml::render($embedded), '<iframe'))->toBe(1)
        ->and(ArticleHtml::render($embedded))->toStartWith('<p><a href="https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO">Follow</a></p>');
});

it('renders nothing for an empty post', function () {
    expect(ArticleHtml::render(null))->toBe('')
        ->and(ArticleHtml::render('<p>&nbsp;</p>'))->toBe('');
});

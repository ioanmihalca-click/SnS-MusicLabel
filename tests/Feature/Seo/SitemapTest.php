<?php

use App\Models\Blog;
use Illuminate\Testing\TestResponse;

/**
 * The <loc> values of the sitemap, which must be well-formed XML.
 *
 * @return list<string>
 */
function sitemapLocations(TestResponse $response): array
{
    $xml = simplexml_load_string($response->getContent());

    expect($xml)->not->toBeFalse();

    return array_map('strval', $xml->xpath('/*[local-name()="urlset"]/*[local-name()="url"]/*[local-name()="loc"]'));
}

it('lists the homepage, the blog and its published posts as XML', function () {
    config(['app.url' => 'https://snow-n-stuff.com']);
    $post = Blog::factory()->published()->create(['slug' => 'live-post', 'cover_image' => 'blog-covers/live.jpg']);
    Blog::factory()->scheduled()->create(['slug' => 'scheduled-post']);
    Blog::factory()->draft()->create(['slug' => 'draft-post']);

    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee('<image:loc>'.$post->coverUrl().'</image:loc>', escape: false);
    expect(sitemapLocations($response))->toBe([
        'https://snow-n-stuff.com/',
        'https://snow-n-stuff.com/blog',
        'https://snow-n-stuff.com/blog/live-post',
    ]);
});

it('lists a new post as soon as it is saved', function () {
    $this->get('/sitemap.xml')->assertOk();

    Blog::factory()->published()->create(['slug' => 'fresh-post']);

    expect(sitemapLocations($this->get('/sitemap.xml')))->toContain(config('app.url').'/blog/fresh-post');
});

it('picks up a scheduled post within an hour of it going live', function () {
    Blog::factory()->create(['slug' => 'scheduled-post', 'published_at' => now()->addMinutes(30)]);

    expect(sitemapLocations($this->get('/sitemap.xml')))->not->toContain(config('app.url').'/blog/scheduled-post');

    $this->travel(61)->minutes();

    expect(sitemapLocations($this->get('/sitemap.xml')))->toContain(config('app.url').'/blog/scheduled-post');
});

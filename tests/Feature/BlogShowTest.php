<?php

use App\Models\Blog;
use Dom\Element;
use Dom\HTMLDocument;
use Illuminate\Testing\TestResponse;

/**
 * The titles listed under "More news".
 *
 * @return list<string>
 */
function relatedTitles(TestResponse $response): array
{
    $headings = HTMLDocument::createFromString($response->getContent(), LIBXML_NOERROR)->querySelectorAll('#more-news article h3');

    return array_map(fn (Element $heading): string => trim($heading->textContent), iterator_to_array($headings, false));
}

it('renders a published post by slug', function () {
    $post = Blog::factory()->published()->create([
        'title' => 'Hello World Post',
    ]);

    $this->get("/blog/{$post->slug}")
        ->assertOk()
        ->assertSeeText('Hello World Post');
});

it('returns 404 for a future-dated post', function () {
    $post = Blog::factory()->scheduled()->create();

    $this->get("/blog/{$post->slug}")->assertNotFound();
});

it('returns 404 for an unknown slug', function () {
    $this->get('/blog/nonexistent-slug')->assertNotFound();
});

it('lists the three newest other posts under More news', function () {
    $current = Blog::factory()->create(['title' => 'Current Article', 'published_at' => now()->subDays(2)]);
    Blog::factory()->create(['title' => 'Oldest', 'published_at' => now()->subDays(10)]);
    Blog::factory()->create(['title' => 'Newer', 'published_at' => now()->subDays(3)]);
    Blog::factory()->create(['title' => 'Newest', 'published_at' => now()->subDay()]);
    Blog::factory()->create(['title' => 'Older', 'published_at' => now()->subDays(5)]);
    Blog::factory()->scheduled()->create(['title' => 'Scheduled']);

    $response = $this->get("/blog/{$current->slug}")->assertOk();

    expect(relatedTitles($response))->toBe(['Newest', 'Newer', 'Older']);
});

it('shows the post in the site layout with its date, cover, share links and a way back', function () {
    $post = Blog::factory()->create([
        'title' => 'Speak To Me Is Out',
        'published_at' => '2025-04-04 10:00:00',
        'cover_image' => 'blog-covers/speak-to-me.jpg',
        'content' => '<p style="color: #ba372a;"><span>Our new single is <strong>out now</strong>.</span></p><p>&nbsp;</p>',
    ]);

    $response = $this->get("/blog/{$post->slug}");

    $response->assertOk()
        ->assertSeeInOrder(['News', '04.04.2025', 'Speak To Me Is Out'])
        ->assertSee('src="'.$post->coverUrl().'"', escape: false)
        ->assertSee('<p>Our new single is <strong>out now</strong>.</p>', escape: false)
        ->assertDontSee('#ba372a', escape: false)
        ->assertDontSee('<p>&nbsp;</p>', escape: false)
        ->assertSee('https://www.facebook.com/sharer/sharer.php?u=', escape: false)
        ->assertSee('href="'.route('blog.index').'"', escape: false);
});

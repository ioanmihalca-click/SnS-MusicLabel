<?php

use App\Models\Blog;

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

it('does not list the current article in related articles', function () {
    $current = Blog::factory()->published()->create(['title' => 'Current Article']);
    $other = Blog::factory()->published()->create(['title' => 'Sibling Article']);

    $response = $this->get("/blog/{$current->slug}");

    $response->assertOk()
        ->assertSeeText('Current Article')
        ->assertSeeText('Sibling Article');

    // The current article's title appears in <h1>; ensure related grid does not duplicate it.
    expect(substr_count($response->getContent(), 'Current Article'))->toBe(1);
});

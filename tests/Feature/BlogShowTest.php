<?php

use App\Models\Blog;
use Illuminate\Support\Str;

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

    // The title also appears in the <head> and the JSON-LD, so only the related grid is searched.
    $relatedArticles = Str::after($response->getContent(), 'Related <span class="text-red-800">Articles</span>');

    expect($relatedArticles)->toContain('Sibling Article')
        ->not->toContain('Current Article');
});

<?php

use App\Models\Blog;

it('responds 200 for /blog', function () {
    $this->get('/blog')->assertOk();
});

it('lists published posts and excludes future-dated ones', function () {
    Blog::factory()->published()->create(['title' => 'Published Post One']);
    Blog::factory()->scheduled()->create(['title' => 'Scheduled Future Post']);

    $this->get('/blog')
        ->assertOk()
        ->assertSeeText('Published Post One')
        ->assertDontSeeText('Scheduled Future Post');
});

it('shows the empty state when there are no published posts', function () {
    $this->get('/blog')
        ->assertOk()
        ->assertSeeText('No posts found');
});

it('is titled News, with the newest post shown first and large', function () {
    Blog::factory()->create(['title' => 'Older Post', 'published_at' => now()->subWeek(), 'cover_image' => 'blog-covers/older.jpg']);
    $newest = Blog::factory()->create(['title' => 'Newest Post', 'published_at' => now()->subDay(), 'meta_description' => 'The newest summary.']);

    $response = $this->get('/blog')->assertOk();

    $response->assertSee('<title>News - Snow &#039;n&#039; Stuff</title>', escape: false)
        ->assertSeeInOrder(['<h1', 'News', 'Newest Post', 'The newest summary.', 'Older Post'], escape: false)
        ->assertSee('src="'.asset('storage/blog-covers/older.jpg').'"', escape: false)
        ->assertSee('loading="lazy"', escape: false)
        ->assertSee('href="'.route('blog.show', $newest->slug).'"', escape: false);
    expect(collect(jsonLd($response)['@graph'])->firstWhere('@type', 'BreadcrumbList')['itemListElement'][1]['name'])->toBe('News');
});

it('links the next pages with real URLs', function () {
    Blog::factory()->count(11)->create();

    $this->get('/blog')
        ->assertOk()
        ->assertSee('href="'.url('/blog').'?page=2"', escape: false)
        ->assertSee("wire:click.prevent=\"nextPage('page')\"", escape: false);
});

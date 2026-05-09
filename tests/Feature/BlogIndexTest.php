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

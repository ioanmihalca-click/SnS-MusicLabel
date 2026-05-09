<?php

use App\Models\Blog;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['email' => 'contact@snow-n-stuff.com']);
    $this->actingAs($this->admin);
});

it('renders the blogs index', function () {
    Blog::factory()->count(2)->create();

    $this->get('/admin/blogs')->assertOk();
});

it('renders the create blog page', function () {
    $this->get('/admin/blogs/create')->assertOk();
});

it('renders the edit blog page', function () {
    $blog = Blog::factory()->create();

    $this->get("/admin/blogs/{$blog->id}/edit")->assertOk();
});

it('preserves the existing slug across title edits', function () {
    $blog = Blog::factory()->create([
        'title' => 'Original Title',
        'slug' => 'original-title',
    ]);

    $blog->title = 'Brand New Title';
    $blog->save();

    expect($blog->fresh()->slug)->toBe('original-title');
});

it('auto-generates a slug for new posts when none is provided', function () {
    $blog = Blog::create([
        'title' => 'Fresh Article',
        'content' => '<p>body</p>',
        'published_at' => now()->subDay(),
    ]);

    expect($blog->slug)->toBe('fresh-article');
});

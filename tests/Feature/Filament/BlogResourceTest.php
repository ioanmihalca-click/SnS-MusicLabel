<?php

use App\Filament\Resources\BlogResource;
use App\Filament\Resources\BlogResource\Pages\CreateBlog;
use App\Filament\Resources\BlogResource\Pages\EditBlog;
use App\Models\Blog;
use App\Models\User;
use Livewire\Livewire;

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

it('warns that saving a post with embedded players removes them', function () {
    $blog = Blog::factory()->create([
        'content' => '<p>Out now.</p><p><iframe src="https://open.spotify.com/embed/album/3zifCl5R2DaZGEmrPNUM1N"></iframe></p>',
    ]);

    Livewire::test(EditBlog::class, ['record' => $blog->getRouteKey()])
        ->assertSee('Embedded players')
        ->assertSee(BlogResource::EMBEDS_WARNING);
});

it('shows no warning for posts without embedded players', function () {
    $blog = Blog::factory()->create(['content' => '<p>Out now on <a href="https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N">Spotify</a>.</p>']);

    Livewire::test(EditBlog::class, ['record' => $blog->getRouteKey()])
        ->assertDontSee(BlogResource::EMBEDS_WARNING);
    Livewire::test(CreateBlog::class)
        ->assertDontSee(BlogResource::EMBEDS_WARNING);
});

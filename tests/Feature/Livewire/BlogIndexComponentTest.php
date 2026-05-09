<?php

use App\Livewire\BlogIndex;
use App\Models\Blog;
use Livewire\Livewire;

it('only lists published posts', function () {
    Blog::factory()->published()->create(['title' => 'Live Article']);
    Blog::factory()->scheduled()->create(['title' => 'Future Article']);
    Blog::factory()->draft()->create(['title' => 'Draft Article']);

    Livewire::test(BlogIndex::class)
        ->assertSee('Live Article')
        ->assertDontSee('Future Article')
        ->assertDontSee('Draft Article');
});

it('filters by search across title and content', function () {
    Blog::factory()->published()->create(['title' => 'Tech House Tutorial', 'content' => '<p>About house mixing.</p>']);
    Blog::factory()->published()->create(['title' => 'Random Other Topic', 'content' => '<p>Unrelated content.</p>']);

    Livewire::test(BlogIndex::class)
        ->set('search', 'tech house')
        ->assertSee('Tech House Tutorial')
        ->assertDontSee('Random Other Topic');
});

it('paginates the listing', function () {
    Blog::factory()->published()->count(15)->create();

    $blogs = Livewire::test(BlogIndex::class)->viewData('blogs');

    expect($blogs->perPage())->toBe(10);
    expect($blogs->total())->toBe(15);
});

it('resets pagination when the search term changes', function () {
    Blog::factory()->published()->count(15)->create();

    Livewire::test(BlogIndex::class)
        ->call('gotoPage', 2)
        ->assertSet('paginators.page', 2)
        ->set('search', 'whatever')
        ->assertSet('paginators.page', 1);
});

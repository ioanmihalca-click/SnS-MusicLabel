<?php

use App\Livewire\PhotoGallery;
use App\Models\Photo;
use Livewire\Livewire;

it('paginates photos at 12 per page', function () {
    Photo::factory()->count(20)->create();

    $photos = Livewire::test(PhotoGallery::class)->viewData('photos');

    expect($photos->perPage())->toBe(12);
    expect($photos->count())->toBe(12);
    expect($photos->total())->toBe(20);
});

it('orders photos newest first', function () {
    $older = Photo::factory()->create(['title' => 'Older', 'created_at' => now()->subDay()]);
    $newer = Photo::factory()->create(['title' => 'Newer', 'created_at' => now()]);

    $photos = Livewire::test(PhotoGallery::class)->viewData('photos');

    expect($photos->first()->title)->toBe('Newer');
});

it('renders the redesigned grid on the about page', function () {
    Photo::factory()->create(['title' => 'In the studio', 'image_path' => 'photos/studio.jpg']);

    Livewire::test(PhotoGallery::class, ['variant' => PhotoGallery::VARIANT_SITE])
        ->assertViewIs('livewire.photo-gallery-site')
        ->assertSee('alt="In the studio"', escape: false)
        ->assertSee('data-fancybox="gallery"', escape: false)
        ->assertDontSee('Some photos of Our Artists');
});

it('keeps the legacy look on the homepage', function () {
    Livewire::test(PhotoGallery::class)
        ->assertViewIs('livewire.photo-gallery')
        ->assertSee('Some photos of Our Artists');
});

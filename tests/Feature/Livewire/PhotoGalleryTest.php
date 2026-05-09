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

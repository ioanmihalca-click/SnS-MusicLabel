<?php

use App\Livewire\PlaylistSlider;
use App\Models\Playlist;
use Livewire\Livewire;

it('only mounts active playlists', function () {
    Playlist::factory()->create(['order' => 1, 'is_active' => true]);
    Playlist::factory()->inactive()->create(['order' => 2]);
    Playlist::factory()->create(['order' => 3, 'is_active' => true]);

    $count = Livewire::test(PlaylistSlider::class)->get('playlists')->count();

    expect($count)->toBe(2);
});

it('cycles forward and wraps to start', function () {
    Playlist::factory()->count(3)->create(['is_active' => true]);

    Livewire::test(PlaylistSlider::class)
        ->assertSet('currentIndex', 0)
        ->call('nextSlide')
        ->assertSet('currentIndex', 1)
        ->call('nextSlide')
        ->assertSet('currentIndex', 2)
        ->call('nextSlide')
        ->assertSet('currentIndex', 0);
});

it('cycles backward and wraps to end', function () {
    Playlist::factory()->count(3)->create(['is_active' => true]);

    Livewire::test(PlaylistSlider::class)
        ->call('previousSlide')
        ->assertSet('currentIndex', 2)
        ->call('previousSlide')
        ->assertSet('currentIndex', 1);
});

it('jumps directly to a specific slide via goToSlide', function () {
    Playlist::factory()->count(4)->create(['is_active' => true]);

    Livewire::test(PlaylistSlider::class)
        ->call('goToSlide', 2)
        ->assertSet('currentIndex', 2);
});

<?php

use App\Livewire\FeaturedTrack;
use App\Models\FeaturedTrack as FeaturedTrackModel;
use Livewire\Livewire;

it('renders without errors when no track exists', function () {
    Livewire::test(FeaturedTrack::class)
        ->assertOk()
        ->assertViewHas('track', null);
});

it('renders the active track with title and artist', function () {
    FeaturedTrackModel::factory()->create([
        'title' => 'Northern Lights',
        'artist_name' => 'Aurora Crew',
    ]);

    Livewire::test(FeaturedTrack::class)
        ->assertOk()
        ->assertSeeText('Now Spinning')
        ->assertSeeText('Northern Lights')
        ->assertSeeText('Aurora Crew');
});

it('ignores inactive tracks', function () {
    FeaturedTrackModel::factory()->inactive()->create([
        'title' => 'Hidden Track',
        'artist_name' => 'Silent Artist',
    ]);

    Livewire::test(FeaturedTrack::class)
        ->assertOk()
        ->assertViewHas('track', null)
        ->assertDontSeeText('Hidden Track');
});

it('orders by ascending order then descending released_at', function () {
    FeaturedTrackModel::factory()->create([
        'title' => 'Older High Order',
        'order' => 5,
        'released_at' => now()->subDays(1),
    ]);
    FeaturedTrackModel::factory()->create([
        'title' => 'Recent Low Order',
        'order' => 1,
        'released_at' => now()->subDays(10),
    ]);
    FeaturedTrackModel::factory()->create([
        'title' => 'Older Low Order',
        'order' => 1,
        'released_at' => now()->subDays(20),
    ]);

    /** @var FeaturedTrackModel $track */
    $track = Livewire::test(FeaturedTrack::class)->viewData('track');

    expect($track->title)->toBe('Recent Low Order');
});

it('derives spotify track id and embed src from the canonical url', function () {
    $track = FeaturedTrackModel::factory()->create([
        'spotify_track_url' => 'https://open.spotify.com/track/4cOdK2wGLETKBW3PvgPWqT?si=abc',
    ]);

    expect($track->spotify_track_id)->toBe('4cOdK2wGLETKBW3PvgPWqT')
        ->and($track->spotify_embed_src)->toBe('https://open.spotify.com/embed/track/4cOdK2wGLETKBW3PvgPWqT');
});

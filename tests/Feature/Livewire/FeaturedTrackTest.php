<?php

use App\Livewire\FeaturedTrack;
use App\Models\Artist;
use App\Models\Release;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('renders nothing when no release is featured', function () {
    Release::factory()->create(['title' => 'Regular Release']);

    Livewire::test(FeaturedTrack::class)
        ->assertViewHas('release', null)
        ->assertDontSeeText('Now Spinning')
        ->assertDontSeeText('Regular Release');
});

it('shows the featured release with its credit, uploaded cover and Spotify link', function () {
    Release::factory()->featured()->create([
        'title' => 'Northern Lights',
        'artist_display' => 'Aurora Crew',
        'cover_image' => 'release-covers/northern-lights.jpg',
        'spotify_url' => 'https://open.spotify.com/album/7CFIxXJQ64nWqnMBFOZkyD',
    ]);

    Livewire::test(FeaturedTrack::class)
        ->assertSeeText('Now Spinning')
        ->assertSeeText('Northern Lights')
        ->assertSeeText('Aurora Crew')
        ->assertSeeHtml('src="'.asset('storage/release-covers/northern-lights.jpg').'"')
        ->assertSeeHtml('href="https://open.spotify.com/album/7CFIxXJQ64nWqnMBFOZkyD"');
});

it('credits the linked roster artists when no credit is typed', function () {
    $release = Release::factory()->featured()->create([
        'artist_display' => null,
        'cover_image' => 'release-covers/cover.jpg',
    ]);
    $release->artists()->attach([
        Artist::factory()->create(['name' => 'THK', 'order' => 2])->id,
        Artist::factory()->create(['name' => 'G&S', 'order' => 1])->id,
    ]);

    Livewire::test(FeaturedTrack::class)
        ->assertSeeText('G&S & THK');
});

it('falls back to the Spotify thumbnail until a cover is uploaded', function () {
    Http::fake([
        'open.spotify.com/oembed*' => Http::response(['thumbnail_url' => 'https://image-cdn-ak.spotifycdn.com/image/human-made']),
    ]);
    Release::factory()->featured()->create([
        'cover_image' => null,
        'spotify_url' => 'https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr',
    ]);

    Livewire::test(FeaturedTrack::class)
        ->assertSeeHtml('src="https://image-cdn-ak.spotifycdn.com/image/human-made"');
});

<?php

use App\Models\Artist;
use App\Models\FeaturedTrack;
use App\Models\Photo;
use App\Models\Playlist;
use App\Models\Release;

it('responds 200 for the homepage', function () {
    $this->get('/')->assertOk();
});

it('renders every documented homepage section so nothing silently disappears', function () {
    Artist::factory()->create(['name' => 'Test Artist', 'order' => 1]);
    Release::factory()->create(['title' => 'Test Release']);
    Playlist::factory()->create(['order' => 1]);
    Photo::factory()->create(['title' => 'Photo One']);
    FeaturedTrack::factory()->create([
        'title' => 'Featured One',
        'artist_name' => 'Featured Artist',
    ]);

    $response = $this->get('/');

    $response->assertOk();

    // Hero
    $response->assertSeeText('Welcome to');
    $response->assertSeeText('Music Management, Label and Music Production');

    // Featured Track (Stage D)
    $response->assertSeeText('Now Spinning');
    $response->assertSeeText('Featured One');
    $response->assertSeeText('Featured Artist');

    // About
    $response->assertSeeText('About');

    // Artists
    $response->assertSeeText('Artists');
    $response->assertSeeText('Test Artist');

    // Releases
    $response->assertSeeText('Check Our Releases');
    $response->assertSeeText('Releases');

    // Playlists
    $response->assertSeeText('Our Curated Collections');

    // Photo Gallery
    $response->assertSeeText('Some photos of Our Artists');

    // Contact
    $response->assertSeeText('Contact Us');
    $response->assertSeeText('Stockholm & Romania', escape: false);
    $response->assertSeeText('Connect With Us');

    // Footer
    $response->assertSeeText('Quick Links');
    $response->assertSeeText('Click Studios Digital');
    $response->assertSeeText('All rights reserved');
});

it('exposes the JSON-LD organization schema', function () {
    $this->get('/')
        ->assertSeeText('"@type": "Organization"', escape: false)
        ->assertSeeText('"foundingDate": "2020"', escape: false);
});

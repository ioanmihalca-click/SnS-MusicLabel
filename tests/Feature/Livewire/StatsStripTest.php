<?php

use App\Livewire\StatsStrip;
use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Release;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::forget('homepage.stats-strip');
});

it('renders the stats strip with all 5 cards labelled', function () {
    Livewire::test(StatsStrip::class)
        ->assertOk()
        ->assertSeeText('Artists')
        ->assertSeeText('Releases')
        ->assertSeeText('Playlists')
        ->assertSeeText('Genres')
        ->assertSeeText('Established');
});

it('counts seeded artists, releases, and active playlists', function () {
    Artist::factory()->count(3)->create();
    Release::factory()->count(7)->create();
    Playlist::factory()->count(2)->create(['is_active' => true]);
    Playlist::factory()->inactive()->create();

    $stats = Livewire::test(StatsStrip::class)->viewData('stats');

    expect($stats[0])->toMatchArray(['value' => 3, 'label' => 'Artists'])
        ->and($stats[1])->toMatchArray(['value' => 7, 'label' => 'Releases'])
        ->and($stats[2])->toMatchArray(['value' => 2, 'label' => 'Playlists'])
        ->and($stats[3]['value'])->toBe(4)   // Genres (hardcoded)
        ->and($stats[4]['value'])->toBe(2020); // Established
});

it('caches the result so subsequent renders avoid recounting', function () {
    Artist::factory()->count(2)->create();

    Livewire::test(StatsStrip::class); // primes cache with value 2

    Artist::factory()->count(5)->create(); // would push count to 7 if uncached

    $stats = Livewire::test(StatsStrip::class)->viewData('stats');

    expect($stats[0]['value'])->toBe(2); // still the cached value
});

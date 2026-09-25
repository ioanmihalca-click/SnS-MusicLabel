<?php

use App\Livewire\Releases;
use App\Models\Release;
use Livewire\Livewire;

it('orders releases newest first', function () {
    Release::factory()->create(['title' => 'Oldest', 'created_at' => now()->subDays(3)]);
    Release::factory()->create(['title' => 'Middle', 'created_at' => now()->subDay()]);
    Release::factory()->create(['title' => 'Latest', 'created_at' => now()]);

    $titles = Livewire::test(Releases::class)
        ->viewData('releases')
        ->pluck('title')
        ->all();

    expect($titles)->toBe(['Latest', 'Middle', 'Oldest']);
});

it('caps the homepage releases query at 24 records', function () {
    Release::factory()->count(30)->create();

    $count = Livewire::test(Releases::class)->viewData('releases')->count();

    expect($count)->toBe(24);
});

it('exposes server-side truncation attributes on releases', function () {
    Release::factory()->create([
        'description' => '<p>'.str_repeat('x', 500).'</p>',
    ]);

    /** @var Release $release */
    $release = Livewire::test(Releases::class)->viewData('releases')->first();

    expect($release->is_truncated)->toBeTrue();
    expect($release->plain_description)->not->toContain('<p>');
});

it('renders a Spotify player only for releases with a Spotify link', function () {
    Release::factory()->create(['spotify_url' => 'https://open.spotify.com/track/3kxXDXxBbNYcwoKJasfW8X']);
    Release::factory()->create(['spotify_url' => null, 'smartlink_url' => 'https://distrokid.com/hyperfollow/snownstuff/soon']);

    $html = Livewire::test(Releases::class)->html();

    expect($html)->toContain('src="https://open.spotify.com/embed/track/3kxXDXxBbNYcwoKJasfW8X"')
        ->and(substr_count($html, '<iframe'))->toBe(1);
});

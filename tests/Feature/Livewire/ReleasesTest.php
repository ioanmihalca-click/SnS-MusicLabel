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

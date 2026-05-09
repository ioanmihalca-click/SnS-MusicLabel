<?php

use App\Livewire\Artists;
use App\Models\Artist;
use Livewire\Livewire;

it('orders artists by the order column', function () {
    Artist::factory()->create(['name' => 'Third', 'order' => 30]);
    Artist::factory()->create(['name' => 'First', 'order' => 10]);
    Artist::factory()->create(['name' => 'Second', 'order' => 20]);

    Livewire::test(Artists::class)
        ->assertSeeTextInOrder(['First', 'Second', 'Third']);
});

it('exposes server-side description preview attributes', function () {
    Artist::factory()->create([
        'description' => '<p>'.str_repeat('a', 500).'</p>',
    ]);

    $component = Livewire::test(Artists::class);

    /** @var Artist $artist */
    $artist = $component->viewData('artists')->first();

    expect($artist->is_truncated)->toBeTrue();
    expect(mb_strlen($artist->short_description))->toBeLessThanOrEqual(225); // 220 + "..." ellipsis
    expect($artist->plain_description)->toContain('aaa');
});

it('keeps short descriptions intact (not truncated)', function () {
    Artist::factory()->create([
        'description' => 'Short bio.',
    ]);

    $artist = Livewire::test(Artists::class)->viewData('artists')->first();

    expect($artist->is_truncated)->toBeFalse();
    expect($artist->plain_description)->toBe('Short bio.');
});

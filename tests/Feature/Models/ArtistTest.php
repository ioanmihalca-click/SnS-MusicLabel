<?php

use App\Models\Artist;

it('generates the slug from the name', function (string $name, string $slug) {
    expect(Artist::factory()->create(['name' => $name])->slug)->toBe($slug);
})->with([
    'plain name' => ['Style da Kid', 'style-da-kid'],
    'ampersand' => ['G&S', 'g-and-s'],
]);

it('keeps the slug when the artist is renamed', function () {
    $artist = Artist::factory()->create(['name' => 'Snow n Stuff']);

    $artist->update(['name' => 'Snow N Stuff']);

    expect($artist->fresh()->slug)->toBe('snow-n-stuff');
});

it('adds a numeric suffix to a duplicate slug', function () {
    Artist::factory()->create(['name' => 'THK']);

    expect(Artist::factory()->create(['name' => 'thk'])->slug)->toBe('thk-2');
});

it('keeps an explicit slug', function () {
    expect(Artist::factory()->create(['name' => 'G&S', 'slug' => 'gs'])->slug)->toBe('gs');
});

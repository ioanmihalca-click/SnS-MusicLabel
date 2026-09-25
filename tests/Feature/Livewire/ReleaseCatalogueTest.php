<?php

use App\Enums\Genre;
use App\Enums\ReleaseFormat;
use App\Livewire\ReleaseCatalogue;
use App\Models\Artist;
use App\Models\Release;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

beforeEach(function () {
    fakeSpotifyThumbnails();

    $gAndS = Artist::factory()->create(['name' => 'G&S', 'order' => 1]);
    $snow = Artist::factory()->create(['name' => 'Snow N Stuff', 'order' => 0]);

    Release::factory()->create(['title' => 'Speak To Me', 'released_at' => '2025-04-04', 'format' => ReleaseFormat::Single, 'genre' => Genre::MelodicTechno])->artists()->attach($snow);
    Release::factory()->create(['title' => 'Frozen in Time', 'released_at' => '2025-06-27', 'format' => ReleaseFormat::Compilation, 'genre' => Genre::Techno])->artists()->attach($snow);
    Release::factory()->create(['title' => 'Back to Black', 'released_at' => '2024-03-08', 'format' => ReleaseFormat::Single, 'genre' => Genre::DeepHouse])->artists()->attach($gAndS);
    Release::factory()->legacy()->create(['title' => 'Undated']);
});

/**
 * @return list<string>
 */
function catalogueTitles(Testable $component): array
{
    return $component->viewData('releases')->pluck('title')->all();
}

it('lists every release newest first, undated ones last', function () {
    expect(catalogueTitles(Livewire::test(ReleaseCatalogue::class)))
        ->toBe(['Frozen in Time', 'Speak To Me', 'Back to Black', 'Undated']);
});

it('filters by artist, format, genre and year', function (string $filter, string $value, array $titles) {
    expect(catalogueTitles(Livewire::test(ReleaseCatalogue::class)->set($filter, $value)))->toBe($titles);
})->with([
    'artist' => ['artist', 'g-and-s', ['Back to Black']],
    'format' => ['format', 'compilation', ['Frozen in Time']],
    'genre' => ['genre', 'melodic-techno', ['Speak To Me']],
    'year' => ['year', '2025', ['Frozen in Time', 'Speak To Me']],
]);

it('combines filters read from the query string', function () {
    $component = Livewire::withQueryParams(['artist' => 'snow-n-stuff', 'format' => 'single'])->test(ReleaseCatalogue::class);

    expect(catalogueTitles($component))->toBe(['Speak To Me'])
        ->and($component->viewData('isFiltered'))->toBeTrue();
});

it('ignores filter values that are not an artist, a format, a genre or a year', function (array $query) {
    $component = Livewire::withQueryParams($query)->test(ReleaseCatalogue::class);

    expect(catalogueTitles($component))->toHaveCount(4)
        ->and($component->viewData('isFiltered'))->toBeFalse();
})->with([
    'unknown values' => [['artist' => 'nobody', 'format' => 'cassette', 'genre' => 'polka', 'year' => 'abc']],
    'arrays' => [['artist' => ['a'], 'year' => ['2025']]],
    'not a whole year' => [['year' => '2025.5']],
]);

it('answers malformed filter URLs with the full catalogue and the canonical /releases', function (string $query) {
    config(['app.url' => 'https://snow-n-stuff.com']);

    $this->get('/releases?'.$query)
        ->assertOk()
        ->assertSee('<link rel="canonical" href="https://snow-n-stuff.com/releases">', escape: false);
})->with([
    'invalid year' => 'genre=techno&year=abc',
    'array' => 'year[]=2025',
    'JSON object' => 'genre=%7B%22a%22%3A1%7D',
]);

it('clears the filters', function () {
    $component = Livewire::withQueryParams(['genre' => 'techno'])
        ->test(ReleaseCatalogue::class)
        ->call('clearFilters');

    expect(catalogueTitles($component))->toHaveCount(4)
        ->and($component->get('genre'))->toBe('');
});

it('offers only the values present in the catalogue, with their counts', function () {
    $filters = Livewire::test(ReleaseCatalogue::class)->viewData('filters');

    expect($filters['artist']['options'])->toBe(['snow-n-stuff' => 'Snow N Stuff (2)', 'g-and-s' => 'G&S (1)'])
        ->and($filters['format']['options'])->toBe(['single' => 'Single (2)', 'compilation' => 'Compilation (1)'])
        ->and($filters['genre']['options'])->toBe(['deep-house' => 'Deep House (1)', 'techno' => 'Techno (1)', 'melodic-techno' => 'Melodic Techno (1)'])
        ->and($filters['year']['options'])->toBe([2025 => '2025 (2)', 2024 => '2024 (1)']);
});

it('shows an empty state when nothing matches', function () {
    Livewire::test(ReleaseCatalogue::class)
        ->set('year', '1999')
        ->assertSee('No releases match these filters.');
});

it('is the /releases page, in the new layout', function () {
    $this->get('/releases')
        ->assertOk()
        ->assertSeeLivewire(ReleaseCatalogue::class)
        ->assertSee('<html lang="en" class="theme-site bg-ink">', escape: false)
        ->assertSeeText('Speak To Me');
});

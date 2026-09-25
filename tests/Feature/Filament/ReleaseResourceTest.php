<?php

use App\Enums\Genre;
use App\Enums\ReleaseFormat;
use App\Filament\Resources\ReleaseResource\Pages\CreateRelease;
use App\Filament\Resources\ReleaseResource\Pages\EditRelease;
use App\Models\Artist;
use App\Models\Release;
use App\Models\User;
use Filament\Forms\Components\Repeater;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['email' => 'contact@snow-n-stuff.com']);
    $this->actingAs($this->admin);
});

it('renders the releases index', function () {
    Release::factory()->count(2)->create();

    $this->get('/admin/releases')->assertOk();
});

it('renders the create release page', function () {
    $this->get('/admin/releases/create')->assertOk();
});

it('renders the edit release page', function () {
    $release = Release::factory()->withTracks()->create();

    $this->get("/admin/releases/{$release->id}/edit")->assertOk();
});

it('creates a release with its tracklist and artists', function () {
    $undoRepeaterFake = Repeater::fake();
    $artist = Artist::factory()->create();

    Livewire::test(CreateRelease::class)
        ->fillForm([
            'title' => 'Speak To Me',
            'artists' => [$artist->id],
            'format' => ReleaseFormat::Single->value,
            'genre' => Genre::MelodicTechno->value,
            'released_at' => '2025-04-04',
            'spotify_url' => 'https://open.spotify.com/intl-de/album/3zifCl5R2DaZGEmrPNUM1N?si=6e8c4ffed0314a6d',
            'support' => ['Richie Hawtin', 'Don Diablo'],
            'tracks' => [
                ['title' => 'Speak To Me', 'version' => 'Edit', 'duration_seconds' => '3:45'],
                ['title' => 'Speak To Me', 'version' => null, 'duration_seconds' => '6:44'],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $undoRepeaterFake();

    $release = Release::sole();
    expect($release)
        ->title->toBe('Speak To Me')
        ->slug->toBe('speak-to-me')
        ->genre->toBe(Genre::MelodicTechno)
        ->spotify_url->toBe('https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N')
        ->support->toBe(['Richie Hawtin', 'Don Diablo']);
    expect($release->artists->modelKeys())->toBe([$artist->id]);
    expect($release->tracks->map->only(['position', 'version', 'duration_seconds'])->all())->toBe([
        ['position' => 1, 'version' => 'Edit', 'duration_seconds' => 225],
        ['position' => 2, 'version' => null, 'duration_seconds' => 404],
    ]);
});

it('saves an upcoming release with only a smartlink', function () {
    Livewire::test(CreateRelease::class)
        ->fillForm([
            'title' => 'Pre-save Me',
            'format' => ReleaseFormat::Single->value,
            'smartlink_url' => 'https://distrokid.com/hyperfollow/snownstuff/pre-save-me',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Release::sole())
        ->spotify_url->toBeNull()
        ->smartlink_url->toBe('https://distrokid.com/hyperfollow/snownstuff/pre-save-me');
});

it('requires a Spotify link or a smartlink', function () {
    Livewire::test(CreateRelease::class)
        ->fillForm(['title' => 'Nowhere to Listen', 'format' => ReleaseFormat::Single->value])
        ->call('create')
        ->assertHasFormErrors(['spotify_url' => 'required_without']);

    expect(Release::count())->toBe(0);
});

it('rejects a Spotify link that is not an album or a track', function (string $url) {
    Livewire::test(CreateRelease::class)
        ->fillForm([
            'title' => 'Wrong Link',
            'format' => ReleaseFormat::Single->value,
            'spotify_url' => $url,
        ])
        ->call('create')
        ->assertHasFormErrors(['spotify_url']);

    expect(Release::count())->toBe(0);
})->with([
    'playlist' => ['https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO'],
    'short link' => ['https://spotify.link/aBcD3fGh1j'],
    'another site' => ['https://soundcloud.com/snow-n-stuff/the-change'],
]);

it('rejects a track duration that is not m:ss', function () {
    $undoRepeaterFake = Repeater::fake();

    Livewire::test(CreateRelease::class)
        ->fillForm([
            'title' => 'Bad Duration',
            'format' => ReleaseFormat::Single->value,
            'spotify_url' => 'https://open.spotify.com/track/3kxXDXxBbNYcwoKJasfW8X',
            'tracks' => [['title' => 'The Change', 'duration_seconds' => '280']],
        ])
        ->call('create')
        ->assertHasFormErrors(['tracks.0.duration_seconds' => 'regex']);

    $undoRepeaterFake();

    expect(Release::count())->toBe(0);
});

it('shows track durations as m:ss and keeps them when saving', function () {
    $release = Release::factory()->create();
    $track = $release->tracks()->create(['position' => 1, 'title' => 'Fuego', 'duration_seconds' => 273]);

    Livewire::test(EditRelease::class, ['record' => $release->getRouteKey()])
        ->assertFormSet(["tracks.record-{$track->id}.duration_seconds" => '4:33'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($track->refresh()->duration_seconds)->toBe(273);
});

it('keeps the slug when the title is edited in the form', function () {
    $release = Release::factory()->create(['title' => 'Snow N Stuff - Fuego']);

    Livewire::test(EditRelease::class, ['record' => $release->getRouteKey()])
        ->fillForm(['title' => 'Fuego'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($release->refresh())
        ->title->toBe('Fuego')
        ->slug->toBe('snow-n-stuff-fuego');
});

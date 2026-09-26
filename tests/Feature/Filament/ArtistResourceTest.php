<?php

use App\Filament\Resources\ArtistResource\Pages\CreateArtist;
use App\Models\Artist;
use App\Models\User;
use Filament\Forms\Components\Repeater;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['email' => 'contact@snow-n-stuff.com']);
    $this->actingAs($this->admin);
});

it('renders the artists index', function () {
    Artist::factory()->count(3)->create();

    $this->get('/admin/artists')->assertOk();
});

it('renders the create artist page', function () {
    $this->get('/admin/artists/create')->assertOk();
});

it('renders the edit artist page', function () {
    $artist = Artist::factory()->create();

    $this->get("/admin/artists/{$artist->id}/edit")->assertOk();
});

it('creates an artist with a profile, links and highlights', function () {
    $undoRepeaterFake = Repeater::fake();

    Livewire::test(CreateArtist::class)
        ->fillForm([
            'name' => 'G&S',
            'order' => 1,
            'role' => 'DJ / producer duo',
            'origin' => 'Glenn Forrestgate (SE) × Style da Kid (RO)',
            'spotify_url' => 'https://open.spotify.com/artist/1ZMBY94RIDI1PLHrxY4iax',
            'instagram_url' => 'https://www.instagram.com/gands',
            'description' => '<p>Deep House, Tech House and Techno.</p>',
            'highlights' => [
                ['highlight' => 'Back to Black reached #15 on Music Week Upfront (UK)'],
                ['highlight' => 'Played on BBC shows'],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $undoRepeaterFake();

    expect(Artist::sole())
        ->slug->toBe('g-and-s')
        ->role->toBe('DJ / producer duo')
        ->instagram_url->toBe('https://www.instagram.com/gands')
        ->highlights->toBe(['Back to Black reached #15 on Music Week Upfront (UK)', 'Played on BBC shows']);
});

it('rejects social links that are not URLs', function () {
    Livewire::test(CreateArtist::class)
        ->fillForm([
            'name' => 'THK',
            'order' => 2,
            'spotify_url' => 'https://open.spotify.com/artist/6YOmRPkzjX9bbTl6Qi2WPy',
            'description' => '<p>Duo.</p>',
            'soundcloud_url' => 'soundcloud thk',
        ])
        ->call('create')
        ->assertHasFormErrors(['soundcloud_url' => 'url']);

    expect(Artist::count())->toBe(0);
});

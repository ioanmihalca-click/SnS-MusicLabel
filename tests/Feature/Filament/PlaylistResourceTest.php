<?php

use App\Filament\Resources\PlaylistResource\Pages\CreatePlaylist;
use App\Filament\Resources\PlaylistResource\Pages\ListPlaylists;
use App\Models\Playlist;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['email' => 'contact@snow-n-stuff.com']);
    $this->actingAs($this->admin);
});

it('renders the playlists index', function () {
    Playlist::factory()->count(2)->create();

    $this->get('/admin/playlists')->assertOk();
});

it('renders the create playlist page', function () {
    $this->get('/admin/playlists/create')->assertOk();
});

it('renders the edit playlist page', function () {
    $playlist = Playlist::factory()->create();

    $this->get("/admin/playlists/{$playlist->id}/edit")->assertOk();
});

it('lists playlists by title', function () {
    Playlist::factory()->create(['title' => 'Chill Ibiza']);

    Livewire::test(ListPlaylists::class)
        ->assertSeeText('Chill Ibiza');
});

it('creates a playlist from a Spotify playlist link', function () {
    Livewire::test(CreatePlaylist::class)
        ->fillForm([
            'spotify_url' => 'https://open.spotify.com/playlist/4SFqIi6db3fdDAIWwHgPSp?si=abc',
            'title' => 'Chill Ibiza',
            'description' => 'Reggae Beach · Beach Bar',
            'order' => 5,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Playlist::sole())
        ->spotify_url->toBe('https://open.spotify.com/playlist/4SFqIi6db3fdDAIWwHgPSp')
        ->title->toBe('Chill Ibiza');
});

it('rejects a Spotify link that is not a playlist', function () {
    Livewire::test(CreatePlaylist::class)
        ->fillForm([
            'spotify_url' => 'https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N',
            'title' => 'Not a Playlist',
        ])
        ->call('create')
        ->assertHasFormErrors(['spotify_url']);

    expect(Playlist::count())->toBe(0);
});

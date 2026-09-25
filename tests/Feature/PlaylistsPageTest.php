<?php

use App\Models\Playlist;

beforeEach(function () {
    config(['app.url' => 'https://snow-n-stuff.com']);
    fakeSpotifyThumbnails('https://image-cdn-ak.spotifycdn.com/image/playlist');
});

it('lists the active playlists in order with a follow link', function () {
    Playlist::factory()->create(['title' => 'Chill Ibiza', 'order' => 2]);
    Playlist::factory()->create([
        'title' => 'Vocal Deep House 2026',
        'description' => 'Melodic Techno · Ibiza',
        'order' => 1,
        'spotify_url' => 'https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO',
    ]);
    Playlist::factory()->inactive()->create(['title' => 'Retired Playlist']);

    $this->get('/playlists')
        ->assertOk()
        ->assertSeeInOrder(['Vocal Deep House 2026', 'Melodic Techno · Ibiza', 'href="https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO"', 'Chill Ibiza'], escape: false)
        ->assertSee('src="https://image-cdn-ak.spotifycdn.com/image/playlist"', escape: false)
        ->assertDontSeeText('Retired Playlist');
});

it('gives a converted playlist without a title a generic one', function () {
    Playlist::factory()->legacy()->create();

    $this->get('/playlists')->assertOk()->assertSeeText(Playlist::FALLBACK_TITLE);
});

it('describes each playlist as a MusicPlaylist curated by the label', function () {
    $playlist = Playlist::factory()->create([
        'title' => 'Ibiza 2026',
        'spotify_url' => 'https://open.spotify.com/playlist/6XKjwYHx4wkYwhMUwkkSp5',
        'cover_image' => 'playlist-covers/ibiza.jpg',
    ]);

    $playlistNode = collect(jsonLd($this->get('/playlists'))['@graph'])->firstWhere('@type', 'MusicPlaylist');

    expect($playlistNode)->toMatchArray([
        '@id' => "https://snow-n-stuff.com/playlists#playlist-{$playlist->id}",
        'name' => 'Ibiza 2026',
        'url' => 'https://open.spotify.com/playlist/6XKjwYHx4wkYwhMUwkkSp5',
        'image' => asset('storage/playlist-covers/ibiza.jpg'),
        'author' => ['@id' => 'https://snow-n-stuff.com/#organization'],
    ]);
});

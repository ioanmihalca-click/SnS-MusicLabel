<?php

use App\Models\Playlist;
use App\Models\User;

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

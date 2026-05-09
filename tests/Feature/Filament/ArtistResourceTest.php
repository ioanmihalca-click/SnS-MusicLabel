<?php

use App\Models\Artist;
use App\Models\User;

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

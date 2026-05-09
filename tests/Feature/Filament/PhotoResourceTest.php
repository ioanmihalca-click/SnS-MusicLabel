<?php

use App\Models\Photo;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['email' => 'contact@snow-n-stuff.com']);
    $this->actingAs($this->admin);
});

it('renders the photos index', function () {
    Photo::factory()->count(2)->create();

    $this->get('/admin/photos')->assertOk();
});

it('renders the create photo page', function () {
    $this->get('/admin/photos/create')->assertOk();
});

it('renders the edit photo page', function () {
    $photo = Photo::factory()->create();

    $this->get("/admin/photos/{$photo->id}/edit")->assertOk();
});

<?php

use App\Models\FeaturedTrack;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['email' => 'contact@snow-n-stuff.com']);
    $this->actingAs($this->admin);
});

it('renders the featured tracks index', function () {
    FeaturedTrack::factory()->count(2)->create();

    $this->get('/admin/featured-tracks')->assertOk();
});

it('renders the create featured track page', function () {
    $this->get('/admin/featured-tracks/create')->assertOk();
});

it('renders the edit featured track page', function () {
    $track = FeaturedTrack::factory()->create();

    $this->get("/admin/featured-tracks/{$track->id}/edit")->assertOk();
});

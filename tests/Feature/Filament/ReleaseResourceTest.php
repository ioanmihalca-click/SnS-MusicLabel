<?php

use App\Models\Release;
use App\Models\User;

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
    $release = Release::factory()->create();

    $this->get("/admin/releases/{$release->id}/edit")->assertOk();
});

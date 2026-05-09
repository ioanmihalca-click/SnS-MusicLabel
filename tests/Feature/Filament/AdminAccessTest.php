<?php

use App\Models\User;

it('redirects unauthenticated visitors to the login page', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('blocks users whose email does not end with contact@snow-n-stuff.com', function () {
    $user = User::factory()->create(['email' => 'someone@gmail.com']);

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('lets authorised admin users reach the dashboard', function () {
    $admin = User::factory()->create(['email' => 'contact@snow-n-stuff.com']);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk();
});

<?php

use App\Livewire\Hero;
use Livewire\Livewire;

it('renders without errors and exposes the brand text', function () {
    Livewire::test(Hero::class)
        ->assertOk()
        ->assertSeeText('Welcome to')
        ->assertSeeText("Snow 'n' Stuff")
        ->assertSeeText('Music Management, Label and Music Production');
});

<?php

use App\Filament\Pages\Manual;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;

it('shows the manual to admins', function () {
    $this->actingAs(User::factory()->create(['email' => 'contact@snow-n-stuff.com']))
        ->get('/admin/manual')
        ->assertOk()
        ->assertSee('Admin manual')
        ->assertSeeInOrder(['Overview', 'Releases', 'Artists', 'Playlists', 'News (Blogs)', 'Photos', 'Demos', 'Privacy &amp; cookies', 'Tips'], escape: false)
        ->assertSee('href="'.route('releases.index').'" target="_blank"', escape: false);
});

it('blocks the manual for users who are not admins', function () {
    $this->actingAs(User::factory()->create(['email' => 'someone@gmail.com']))
        ->get('/admin/manual')
        ->assertForbidden();
});

it('lists the manual last in the navigation, in its own Help group', function () {
    $this->actingAs(User::factory()->create(['email' => 'contact@snow-n-stuff.com']))
        ->get('/admin')
        ->assertOk()
        ->assertSee(Manual::getUrl());

    $navigation = collect(Filament::getNavigation())
        ->filter(fn (NavigationGroup $group): bool => filled($group->getLabel()))
        ->mapWithKeys(fn (NavigationGroup $group): array => [
            $group->getLabel() => collect($group->getItems())->map(fn (NavigationItem $item): string => $item->getLabel())->values()->all(),
        ]);

    expect($navigation->keys()->all())->toBe(['Catalogue', 'Content', 'Label', 'Media', 'Help'])
        ->and($navigation['Help'])->toBe(['Manual']);
});

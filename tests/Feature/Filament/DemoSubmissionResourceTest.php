<?php

use App\Enums\DemoStatus;
use App\Filament\Resources\DemoSubmissionResource;
use App\Filament\Resources\DemoSubmissionResource\Pages\EditDemoSubmission;
use App\Filament\Resources\DemoSubmissionResource\Pages\ListDemoSubmissions;
use App\Models\DemoSubmission;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['email' => 'contact@snow-n-stuff.com']);
    $this->actingAs($this->admin);
});

it('renders the demos index and the review page', function () {
    $demo = DemoSubmission::factory()->create();

    $this->get('/admin/demo-submissions')->assertOk();
    $this->get("/admin/demo-submissions/{$demo->id}/edit")->assertOk();
});

it('has no create page: demos come from the public form only', function () {
    $this->get('/admin/demo-submissions/create')->assertNotFound();

    expect(DemoSubmissionResource::canCreate())->toBeFalse();
});

it('lists the newest demos first, with the link opening in a new tab', function () {
    $older = DemoSubmission::factory()->create(['artist_name' => 'Older', 'created_at' => now()->subWeek()]);
    $newer = DemoSubmission::factory()->create(['artist_name' => 'Newer', 'link' => 'https://on.soundcloud.com/AbCdEfGh']);

    Livewire::test(ListDemoSubmissions::class)
        ->assertCanSeeTableRecords([$newer, $older], inOrder: true)
        ->assertSee('href="https://on.soundcloud.com/AbCdEfGh" target="_blank"', escape: false)
        ->assertTableColumnFormattedStateSet('genre', $newer->genreLabel(), $newer);
});

it('filters the demos by status and genre', function () {
    $new = DemoSubmission::factory()->create(['genre' => 'techno']);
    $declined = DemoSubmission::factory()->declined()->create(['genre' => 'house']);
    $other = DemoSubmission::factory()->create(['genre' => DemoSubmission::OTHER_GENRE]);

    Livewire::test(ListDemoSubmissions::class)
        ->filterTable('status', DemoStatus::Declined->value)
        ->assertCanSeeTableRecords([$declined])
        ->assertCanNotSeeTableRecords([$new, $other]);

    Livewire::test(ListDemoSubmissions::class)
        ->filterTable('genre', DemoSubmission::OTHER_GENRE)
        ->assertCanSeeTableRecords([$other])
        ->assertCanNotSeeTableRecords([$new, $declined]);
});

it('updates only the status and the notes; what the artist sent stays as sent', function () {
    $demo = DemoSubmission::factory()->create([
        'artist_name' => 'G&S',
        'email' => 'gands@example.com',
        'link' => 'https://soundcloud.com/gands/back-to-black',
    ]);

    Livewire::test(EditDemoSubmission::class, ['record' => $demo->getRouteKey()])
        ->assertFormFieldIsDisabled('artist_name')
        ->assertFormFieldIsDisabled('email')
        ->assertFormFieldIsDisabled('link')
        ->assertFormFieldIsDisabled('message')
        ->assertFormFieldIsEnabled('status')
        ->assertFormFieldIsEnabled('notes')
        ->fillForm([
            'artist_name' => 'Someone else',
            'email' => 'else@example.com',
            'link' => 'https://evil.example/x',
            'status' => DemoStatus::Accepted->value,
            'notes' => 'Signed for the summer compilation.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($demo->fresh())
        ->artist_name->toBe('G&S')
        ->email->toBe('gands@example.com')
        ->link->toBe('https://soundcloud.com/gands/back-to-black')
        ->status->toBe(DemoStatus::Accepted)
        ->notes->toBe('Signed for the summer compilation.');
});

it('counts the new demos in the navigation badge', function () {
    expect(DemoSubmissionResource::getNavigationBadge())->toBeNull();

    DemoSubmission::factory()->count(2)->create();
    DemoSubmission::factory()->status(DemoStatus::Listened)->create();

    expect(DemoSubmissionResource::getNavigationBadge())->toBe('2');
    $this->get('/admin/demo-submissions')->assertOk()->assertSee('New demos');
});

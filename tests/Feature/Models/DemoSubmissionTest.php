<?php

use App\Enums\DemoStatus;
use App\Models\DemoSubmission;

it('deletes only the demos older than 12 months that were not accepted', function () {
    $this->travelTo('2026-09-26 12:00:00');

    $old = fn (DemoStatus $status): DemoSubmission => DemoSubmission::factory()->status($status)->create(['created_at' => '2025-09-25 12:00:00']);
    $oldNew = $old(DemoStatus::New);
    $oldListened = $old(DemoStatus::Listened);
    $oldDeclined = $old(DemoStatus::Declined);
    $oldAccepted = $old(DemoStatus::Accepted);
    $recentDeclined = DemoSubmission::factory()->declined()->create(['created_at' => '2025-10-01 12:00:00']);

    $this->artisan('model:prune', ['--model' => [DemoSubmission::class]])->assertSuccessful();

    expect(DemoSubmission::pluck('id')->sort()->values()->all())->toBe([$oldAccepted->id, $recentDeclined->id])
        ->and(DemoSubmission::find($oldNew->id))->toBeNull()
        ->and(DemoSubmission::find($oldListened->id))->toBeNull()
        ->and(DemoSubmission::find($oldDeclined->id))->toBeNull();
});

it('accepts https links to SoundCloud, Dropbox and Google Drive only', function (string $url, bool $isAllowed) {
    expect(DemoSubmission::isAllowedLink($url))->toBe($isAllowed);
})->with([
    'SoundCloud' => ['https://soundcloud.com/gands/back-to-black', true],
    'SoundCloud with www' => ['https://www.soundcloud.com/gands', true],
    'SoundCloud short link' => ['https://on.soundcloud.com/AbCd', true],
    'Dropbox' => ['https://www.dropbox.com/scl/fi/abc/x.wav', true],
    'Google Drive' => ['https://drive.google.com/file/d/abc/view', true],
    'upper-case host' => ['https://SoundCloud.com/gands', true],
    'http' => ['http://soundcloud.com/gands', false],
    'other subdomain' => ['https://m.dropbox.com/x', false],
    'Google Docs' => ['https://docs.google.com/x', false],
    'lookalike' => ['https://dropbox.com.evil.example/x', false],
    'credentials' => ['https://dropbox.com@evil.example/x', false],
    'port' => ['https://soundcloud.com:8443/x', false],
]);

it('labels the genre, "Other" included', function () {
    expect(DemoSubmission::factory()->make(['genre' => 'melodic-techno'])->genreLabel())->toBe('Melodic Techno')
        ->and(DemoSubmission::factory()->make(['genre' => DemoSubmission::OTHER_GENRE])->genreLabel())->toBe('Other')
        ->and(DemoSubmission::factory()->make(['genre' => null])->genreLabel())->toBeNull();
});

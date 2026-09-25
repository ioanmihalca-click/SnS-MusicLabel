<?php

use App\Models\Release;

it('names each supporting DJ once, from the newest releases first', function () {
    $releases = collect([
        Release::factory()->create(['title' => 'The Change', 'support' => ['Paul van Dyk']]),
        Release::factory()->create(['title' => 'Speak To Me', 'support' => ['Richie Hawtin', ' Don  Diablo ', 'paul VAN dyk']]),
        Release::factory()->create(['title' => 'Get Out Of My Head', 'support' => ['Don Diablo', 'Armin van Buuren', '']]),
        Release::factory()->legacy()->create(['title' => 'Warrior']),
    ]);

    $view = $this->blade('<x-home.support :releases="$releases" />', ['releases' => $releases]);

    $view->assertSeeText('Supported & played by')
        ->assertSeeInOrder(['Paul van Dyk', 'Richie Hawtin', 'Don Diablo', 'Armin van Buuren']);
    expect(substr_count(strtolower((string) $view), 'paul van dyk'))->toBe(1)
        ->and(substr_count((string) $view, 'Diablo'))->toBe(1);
});

it('shows the chart positions linked to their releases', function () {
    $releases = collect([
        Release::factory()->create(['title' => 'Speak To Me', 'released_at' => '2025-04-04', 'chart_position' => '#59', 'chart_name' => 'Beatport Hype']),
        Release::factory()->create(['title' => 'Back to Black', 'released_at' => '2024-03-08', 'chart_position' => '#15', 'chart_name' => 'Music Week Upfront (UK)']),
        Release::factory()->create(['title' => 'Uncharted']),
    ]);

    $this->blade('<x-home.support :releases="$releases" />', ['releases' => $releases])
        ->assertSeeInOrder(['#59', 'Beatport Hype', 'Speak To Me · 2025', '#15', 'Music Week Upfront (UK)', 'Back to Black · 2024'])
        ->assertSee('href="'.route('releases.show', 'speak-to-me').'"', escape: false)
        ->assertDontSee('Uncharted');
});

it('is hidden without support or charts', function () {
    $releases = collect([Release::factory()->create(['support' => []]), Release::factory()->legacy()->create()]);

    expect(trim((string) $this->blade('<x-home.support :releases="$releases" />', ['releases' => $releases])))->toBe('');
});

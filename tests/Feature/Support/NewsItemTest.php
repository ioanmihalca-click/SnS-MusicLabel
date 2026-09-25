<?php

use App\Models\Artist;
use App\Models\Blog;
use App\Models\Release;
use App\Support\NewsItem;

beforeEach(function () {
    fakeSpotifyThumbnails();
});

/**
 * The feed as "type: title" lines, newest first.
 *
 * @return list<string>
 */
function newsFeed(): array
{
    return NewsItem::latest()->map(fn (NewsItem $item): string => "{$item->type}: {$item->title}")->all();
}

it('mixes the two newest releases and the two newest posts, newest first', function () {
    foreach (range(1, 3) as $week) {
        Release::factory()->create(['title' => "Release {$week}", 'artist_display' => 'G&S', 'released_at' => now()->subWeeks($week * 2)]);
        Blog::factory()->create(['title' => "Post {$week}", 'published_at' => now()->subWeeks($week * 2 - 1)]);
    }

    expect(newsFeed())->toBe([
        'post: Post 1',
        'release: Release 1 by G&S is out now',
        'post: Post 2',
        'release: Release 2 by G&S is out now',
    ]);
});

it('fills the free places with the other kind', function (int $releases, int $posts, array $expected) {
    foreach (range(1, 4) as $index) {
        if ($index <= $releases) {
            Release::factory()->create(['title' => "Release {$index}", 'released_at' => now()->subDays($index * 2)]);
        }

        if ($index <= $posts) {
            Blog::factory()->create(['title' => "Post {$index}", 'published_at' => now()->subDays($index * 2 + 1)]);
        }
    }

    expect(NewsItem::latest()->map(fn (NewsItem $item): string => str($item->title)->before(' is out now')->toString())->all())->toBe($expected);
})->with([
    'one post' => [4, 1, ['Release 1', 'Post 1', 'Release 2', 'Release 3']],
    'one release' => [1, 4, ['Release 1', 'Post 1', 'Post 2', 'Post 3']],
    'no posts' => [4, 0, ['Release 1', 'Release 2', 'Release 3', 'Release 4']],
    'few of both' => [1, 1, ['Release 1', 'Post 1']],
    'nothing' => [0, 0, []],
]);

it('leaves out upcoming releases and unpublished posts', function () {
    Release::factory()->create(['title' => 'Out Today', 'released_at' => today()]);
    Release::factory()->create(['title' => 'Tomorrow', 'released_at' => today()->addDay()]);
    Release::factory()->legacy()->create(['title' => 'Undated']);
    Blog::factory()->scheduled()->create(['title' => 'Scheduled']);
    Blog::factory()->draft()->create(['title' => 'Draft']);

    expect(newsFeed())->toBe(['release: Out Today is out now']);
});

it('orders entries of the same date by the most recently added', function () {
    $publishedAt = now()->subDay()->startOfMinute();
    Blog::factory()->create(['title' => 'First Added', 'published_at' => $publishedAt]);
    Blog::factory()->create(['title' => 'Second Added', 'published_at' => $publishedAt]);

    expect(newsFeed())->toBe(['post: Second Added', 'post: First Added']);
});

it('credits the roster artists and links to the release page', function () {
    $artist = Artist::factory()->create(['name' => 'Snow N Stuff']);
    $release = Release::factory()->create(['title' => 'I Know', 'released_at' => '2026-07-17', 'cover_image' => 'release-covers/i-know.jpg']);
    $release->artists()->attach($artist);

    $item = NewsItem::latest()->sole();

    expect($item)
        ->title->toBe('I Know by Snow N Stuff is out now')
        ->url->toBe(route('releases.show', 'i-know'))
        ->imageUrl->toBe(asset('storage/release-covers/i-know.jpg'))
        ->and($item->date->toDateString())->toBe('2026-07-17')
        ->and($item->label())->toBe('Release');
});

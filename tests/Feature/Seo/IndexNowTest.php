<?php

use App\Models\Artist;
use App\Models\Blog;
use App\Models\Photo;
use App\Models\Playlist;
use App\Models\Release;
use App\Models\Track;
use App\Support\Seo\IndexNow;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    config(['app.url' => 'https://snow-n-stuff.com']);
});

it('serves the key file containing exactly the key', function () {
    $key = IndexNow::key();

    expect($key)->toMatch('/^[a-f0-9]{32}$/');
    $this->get("/{$key}.txt")
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertContent($key);
});

it('returns 404 for any other key file', function () {
    $this->get('/'.str_repeat('a', 32).'.txt')->assertNotFound();
});

it('submits every changed URL in one request once the response is sent', function () {
    config(['services.indexnow.enabled' => true]);
    Http::fake([IndexNow::ENDPOINT => Http::response(null, 202)]);

    Blog::factory()->published()->create(['slug' => 'speak-to-me']);
    Release::factory()->create(['title' => 'Speak To Me']);

    Http::assertNothingSent();

    defer()->invoke();

    Http::assertSentCount(1);
    Http::assertSent(fn (Request $request): bool => $request->url() === IndexNow::ENDPOINT
        && $request->hasHeader('Content-Type', 'application/json; charset=utf-8')
        && $request['host'] === 'snow-n-stuff.com'
        && $request['key'] === IndexNow::key()
        && $request['keyLocation'] === 'https://snow-n-stuff.com/'.IndexNow::key().'.txt'
        && $request['urlList'] === [
            'https://snow-n-stuff.com/',
            'https://snow-n-stuff.com/blog',
            'https://snow-n-stuff.com/blog/speak-to-me',
            'https://snow-n-stuff.com/releases',
            'https://snow-n-stuff.com/releases/speak-to-me',
        ]);
});

it('submits the pages that show a catalogue item', function (Closure $change, array $urls) {
    $artist = Artist::factory()->create(['name' => 'G&S']);
    $release = Release::factory()->create(['title' => 'Back to Black']);
    $release->artists()->attach($artist);
    config(['services.indexnow.enabled' => true]);
    Http::fake([IndexNow::ENDPOINT => Http::response(null, 202)]);

    $change($release, $artist);
    defer()->invoke();

    Http::assertSent(fn (Request $request): bool => $request['urlList'] === $urls);
})->with([
    'release edited' => [
        fn (Release $release) => $release->update(['genre' => 'house']),
        [
            'https://snow-n-stuff.com/',
            'https://snow-n-stuff.com/releases',
            'https://snow-n-stuff.com/releases/back-to-black',
            'https://snow-n-stuff.com/artists/g-and-s',
        ],
    ],
    'artist slug changed' => [
        fn (Release $release, Artist $artist) => $artist->update(['slug' => 'gs']),
        [
            'https://snow-n-stuff.com/',
            'https://snow-n-stuff.com/artists',
            'https://snow-n-stuff.com/artists/gs',
            'https://snow-n-stuff.com/artists/g-and-s',
            'https://snow-n-stuff.com/releases',
        ],
    ],
    'track edited' => [
        fn (Release $release) => Track::factory()->for($release)->create(),
        ['https://snow-n-stuff.com/releases/back-to-black'],
    ],
    'playlist edited' => [
        fn () => Playlist::factory()->create(),
        ['https://snow-n-stuff.com/', 'https://snow-n-stuff.com/playlists'],
    ],
    'photo added' => [
        fn () => Photo::factory()->create(),
        ['https://snow-n-stuff.com/', 'https://snow-n-stuff.com/about'],
    ],
]);

it('submits both the old and the new URL when a post slug changes', function () {
    $post = Blog::factory()->published()->create(['slug' => 'old-slug']);
    config(['services.indexnow.enabled' => true]);
    Http::fake([IndexNow::ENDPOINT => Http::response(null, 200)]);

    $post->update(['slug' => 'new-slug']);
    defer()->invoke();

    Http::assertSent(fn (Request $request): bool => $request['urlList'] === [
        'https://snow-n-stuff.com/',
        'https://snow-n-stuff.com/blog',
        'https://snow-n-stuff.com/blog/new-slug',
        'https://snow-n-stuff.com/blog/old-slug',
    ]);
});

it('does not submit unpublished posts', function () {
    config(['services.indexnow.enabled' => true]);
    Http::fake([IndexNow::ENDPOINT => Http::response(null, 202)]);

    Blog::factory()->draft()->create();
    Blog::factory()->scheduled()->create();
    defer()->invoke();

    Http::assertNothingSent();
});

it('does not submit anything when disabled, as outside production', function () {
    Http::fake([IndexNow::ENDPOINT => Http::response(null, 202)]);

    Blog::factory()->published()->create();
    defer()->invoke();

    Http::assertNothingSent();
});

it('only logs a failed submission', function (Closure $fakeResponse) {
    config(['services.indexnow.enabled' => true]);
    Http::fake([IndexNow::ENDPOINT => $fakeResponse()]);
    Log::spy();

    Blog::factory()->published()->create();
    defer()->invoke();

    Log::shouldHaveReceived('warning')->once();
})->with([
    'rejected' => [fn () => Http::response(null, 422)],
    'unreachable' => [fn () => Http::failedConnection()],
]);

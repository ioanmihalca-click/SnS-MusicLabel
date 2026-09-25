<?php

use App\Models\Artist;
use App\Models\Blog;
use App\Models\Photo;
use App\Models\Release;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;

/**
 * The <loc> values of the sitemap, which must be well-formed XML.
 *
 * @return list<string>
 */
function sitemapLocations(TestResponse $response): array
{
    $xml = simplexml_load_string($response->getContent());

    expect($xml)->not->toBeFalse();

    return array_map('strval', $xml->xpath('/*[local-name()="urlset"]/*[local-name()="url"]/*[local-name()="loc"]'));
}

it('lists the homepage, the blog and its published posts as XML', function () {
    config(['app.url' => 'https://snow-n-stuff.com']);
    $post = Blog::factory()->published()->create(['slug' => 'live-post', 'cover_image' => 'blog-covers/live.jpg']);
    Blog::factory()->scheduled()->create(['slug' => 'scheduled-post']);
    Blog::factory()->draft()->create(['slug' => 'draft-post']);

    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee('<image:loc>'.$post->coverUrl().'</image:loc>', escape: false);
    expect(sitemapLocations($response))->toBe([
        'https://snow-n-stuff.com/',
        'https://snow-n-stuff.com/about',
        'https://snow-n-stuff.com/playlists',
        'https://snow-n-stuff.com/releases',
        'https://snow-n-stuff.com/artists',
        'https://snow-n-stuff.com/blog',
        'https://snow-n-stuff.com/blog/live-post',
    ]);
});

it('lists every release and artist page', function () {
    config(['app.url' => 'https://snow-n-stuff.com']);
    $artist = Artist::factory()->create(['name' => 'G&S']);
    Release::factory()->create(['title' => 'Back to Black', 'released_at' => '2024-03-08'])->artists()->attach($artist);
    Release::factory()->legacy()->create(['title' => 'Warrior']);

    expect(sitemapLocations($this->get('/sitemap.xml')))->toBe([
        'https://snow-n-stuff.com/',
        'https://snow-n-stuff.com/about',
        'https://snow-n-stuff.com/playlists',
        'https://snow-n-stuff.com/releases',
        'https://snow-n-stuff.com/releases/back-to-black',
        'https://snow-n-stuff.com/releases/warrior',
        'https://snow-n-stuff.com/artists',
        'https://snow-n-stuff.com/artists/g-and-s',
        'https://snow-n-stuff.com/blog',
    ]);
});

it('adds only uploaded artwork or Spotify thumbnails already in cache, without calling Spotify', function () {
    Release::factory()->create(['title' => 'Uploaded', 'cover_image' => 'release-covers/uploaded.jpg']);
    $cached = Release::factory()->create(['title' => 'Cached', 'spotify_url' => 'https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr']);
    Release::factory()->create(['title' => 'Unknown', 'spotify_url' => 'https://open.spotify.com/track/0Co9icwI2q3fAKlJUXu8Si']);
    Photo::factory()->create(['image_path' => 'photos/studio.jpg']);

    fakeSpotifyThumbnails('https://image-cdn-ak.spotifycdn.com/image/cached');
    $cached->coverUrl();

    $response = $this->get('/sitemap.xml')->assertOk();

    Http::assertSentCount(1);
    expect(substr_count($response->getContent(), '<image:loc>'))->toBe(3);
    $response
        ->assertSee('<image:loc>'.asset('storage/release-covers/uploaded.jpg').'</image:loc>', escape: false)
        ->assertSee('<image:loc>https://image-cdn-ak.spotifycdn.com/image/cached</image:loc>', escape: false)
        ->assertSee('<image:loc>'.asset('storage/photos/studio.jpg').'</image:loc>', escape: false);
});

it('lists a new post as soon as it is saved', function () {
    $this->get('/sitemap.xml')->assertOk();

    Blog::factory()->published()->create(['slug' => 'fresh-post']);

    expect(sitemapLocations($this->get('/sitemap.xml')))->toContain(config('app.url').'/blog/fresh-post');
});

it('picks up a scheduled post within an hour of it going live', function () {
    Blog::factory()->create(['slug' => 'scheduled-post', 'published_at' => now()->addMinutes(30)]);

    expect(sitemapLocations($this->get('/sitemap.xml')))->not->toContain(config('app.url').'/blog/scheduled-post');

    $this->travel(61)->minutes();

    expect(sitemapLocations($this->get('/sitemap.xml')))->toContain(config('app.url').'/blog/scheduled-post');
});

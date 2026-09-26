<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\Blog;
use App\Models\Photo;
use App\Models\Playlist;
use App\Models\Release;
use App\Models\User;
use App\Support\Slug;
use App\Support\Spotify\SpotifyUrl;
use Illuminate\Database\Seeder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Recreates the public site's content locally (never in production).
 *
 * The catalogue is seeded the way production looks right after the stage 1
 * migrations (legacy titles and names, Spotify links, descriptions, the featured
 * release), then `catalog:backfill` runs, so a local seed exercises the real
 * backfill path. Blog posts and gallery photos come from snapshot.json; missing
 * images are downloaded from the live site when it is reachable.
 */
class SnapshotSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'contact@snow-n-stuff.com';

    private const FEATURED_RELEASE_ID = '5LoRtT4HMphu4n2OyJn4Cr';

    private bool $isOffline = false;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('SnapshotSeeder recreates local demo content and must never run in production.');
        }

        $catalog = File::json(database_path('data/catalog.json'), JSON_THROW_ON_ERROR);
        $snapshot = File::json(database_path('data/snapshot.json'), JSON_THROW_ON_ERROR);

        $this->seedArtists($catalog['artists']);
        $this->seedReleases($catalog['releases'], $snapshot['release_covers']);
        $this->seedPlaylists($catalog['playlists']);

        $exitCode = Artisan::call('catalog:backfill', [], $this->command?->getOutput());

        if ($exitCode !== 0) {
            throw new RuntimeException('catalog:backfill failed, see its output above.');
        }

        $this->seedPosts($snapshot['posts']);
        $this->seedPhotos($snapshot['photos']);

        $this->downloadMissingFiles($snapshot['storage_base_url'], [
            ...array_values($snapshot['release_covers']),
            ...array_filter(array_column($snapshot['posts'], 'cover_image')),
            ...array_column($snapshot['photos'], 'image_path'),
        ]);

        User::firstOrCreate(
            ['email' => self::ADMIN_EMAIL],
            ['name' => "Snow 'n' Stuff Admin", 'password' => 'password'],
        );
    }

    /**
     * @param  array<string, array<string, mixed>>  $artists  keyed by display name
     */
    private function seedArtists(array $artists): void
    {
        foreach ($artists as $displayName => $artist) {
            Artist::firstOrCreate(
                ['slug' => Slug::from($displayName)],
                [
                    'name' => $artist['legacy_name'] ?? $displayName,
                    'order' => $artist['order'] ?? 0,
                    'spotify_url' => $artist['spotify_url'],
                    'description' => $artist['description'],
                ],
            );
        }
    }

    /**
     * Newest first in the catalogue, so the homepage keeps the live order.
     *
     * @param  array<string, array<string, mixed>>  $releases  keyed by Spotify ID
     * @param  array<string, string>  $covers  storage paths keyed by Spotify ID
     */
    private function seedReleases(array $releases, array $covers): void
    {
        $createdAt = now();

        foreach ($releases as $spotifyId => $release) {
            $spotifyUrl = SpotifyUrl::fromParts($release['type'], $spotifyId)->url();

            if (Release::query()->where('spotify_url', $spotifyUrl)->exists()) {
                continue;
            }

            $createdAt = $createdAt->copy()->subMinute();

            $model = new Release([
                'title' => $release['legacy_title'] ?? $release['title'],
                'description' => $release['description'],
                'spotify_url' => $spotifyUrl,
                'cover_image' => $covers[$spotifyId] ?? null,
                'is_featured' => $spotifyId === self::FEATURED_RELEASE_ID,
            ]);
            $model->created_at = $createdAt;
            $model->updated_at = $createdAt;
            $model->save();
        }
    }

    /**
     * @param  array<string, array<string, mixed>>  $playlists  keyed by Spotify ID
     */
    private function seedPlaylists(array $playlists): void
    {
        foreach ($playlists as $spotifyId => $playlist) {
            Playlist::firstOrCreate(
                ['spotify_url' => SpotifyUrl::fromParts('playlist', $spotifyId)->url()],
                ['order' => $playlist['order'] ?? 0, 'is_active' => true],
            );
        }
    }

    /**
     * @param  list<array<string, mixed>>  $posts
     */
    private function seedPosts(array $posts): void
    {
        foreach ($posts as $post) {
            Blog::firstOrCreate(
                ['slug' => $post['slug']],
                [
                    'title' => $post['title'],
                    'content' => $post['content'],
                    'published_at' => $post['published_at'],
                    'meta_title' => $post['meta_title'] ?? null,
                    'meta_description' => $post['meta_description'] ?? null,
                    'meta_keywords' => $post['meta_keywords'] ?? null,
                    'cover_image' => $post['cover_image'] ?? null,
                ],
            );
        }
    }

    /**
     * Newest first in the snapshot, so the gallery keeps the live order.
     *
     * @param  list<array<string, string>>  $photos
     */
    private function seedPhotos(array $photos): void
    {
        $createdAt = now();

        foreach ($photos as $photo) {
            if (Photo::query()->where('image_path', $photo['image_path'])->exists()) {
                continue;
            }

            $createdAt = $createdAt->copy()->subMinute();

            $model = new Photo(['title' => $photo['title'], 'image_path' => $photo['image_path']]);
            $model->created_at = $createdAt;
            $model->updated_at = $createdAt;
            $model->save();
        }
    }

    /**
     * Copy the live images into the local public disk, skipping files that are
     * already there. Offline (or on any failed download) the seed still completes.
     *
     * @param  list<string>  $paths
     */
    private function downloadMissingFiles(string $baseUrl, array $paths): void
    {
        $disk = Storage::disk('public');

        foreach (array_unique($paths) as $path) {
            if ($this->isOffline || $disk->exists($path)) {
                continue;
            }

            try {
                $response = Http::connectTimeout(3)->timeout(20)->get(rtrim($baseUrl, '/').'/'.ltrim($path, '/'));
            } catch (ConnectionException) {
                $this->isOffline = true;
                $this->command?->warn('Offline: skipping image downloads.');

                continue;
            }

            if (! $response->successful()) {
                $this->command?->warn("Could not download {$path} (HTTP {$response->status()}).");

                continue;
            }

            $disk->put($path, $response->body());
        }
    }
}

<?php

use App\Support\Slug;
use App\Support\Spotify\SpotifyUrl;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Stage 1, step B (data only, idempotent):
 * - the pasted embed HTML becomes a canonical spotify_url on releases and playlists;
 * - every featured_tracks row becomes a release flagged is_featured (or flags the
 *   existing release with the same Spotify type and ID);
 * - releases and artists get slugs.
 *
 * Rows that already have a spotify_url or slug are skipped, so re-running is harmless.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->convertEmbeds('releases', 'spotify_embed_code');
        $this->convertEmbeds('playlists', 'spotify_embed_url');
        $this->convertFeaturedTracks();
        $this->generateSlugs('releases', 'title');
        $this->generateSlugs('artists', 'name');
    }

    /**
     * Data conversion only: rolling back step A drops the columns filled here, and
     * rolling back step D rebuilds the embed HTML and the featured_tracks rows.
     */
    public function down(): void
    {
        //
    }

    private function convertEmbeds(string $table, string $embedColumn): void
    {
        DB::table($table)
            ->whereNull('spotify_url')
            ->whereNotNull($embedColumn)
            ->lazyById()
            ->each(function (object $row) use ($table, $embedColumn): void {
                $spotifyUrl = SpotifyUrl::parse($row->{$embedColumn});

                if ($spotifyUrl === null) {
                    return;
                }

                DB::table($table)->where('id', $row->id)->update(['spotify_url' => $spotifyUrl->url()]);
            });
    }

    private function convertFeaturedTracks(): void
    {
        DB::table('featured_tracks')->orderBy('id')->get()->each(function (object $track): void {
            $spotifyUrl = SpotifyUrl::parse($track->spotify_track_url);

            if ($spotifyUrl === null) {
                return;
            }

            $isFeatured = (bool) $track->is_active;

            $existingReleaseId = DB::table('releases')->where('spotify_url', $spotifyUrl->url())->value('id');

            if ($existingReleaseId !== null) {
                if ($isFeatured) {
                    DB::table('releases')->where('id', $existingReleaseId)->update(['is_featured' => true]);
                }

                return;
            }

            DB::table('releases')->insert([
                'title' => $track->title,
                'artist_display' => $track->artist_name,
                'spotify_url' => $spotifyUrl->url(),
                'cover_image' => $track->cover_image,
                'released_at' => $track->released_at,
                'is_featured' => $isFeatured,
                'format' => 'single',
                'created_at' => $track->created_at,
                'updated_at' => $track->updated_at,
            ]);
        });
    }

    private function generateSlugs(string $table, string $sourceColumn): void
    {
        DB::table($table)
            ->whereNull('slug')
            ->lazyById()
            ->each(function (object $row) use ($table, $sourceColumn): void {
                $slug = Slug::unique(
                    (string) $row->{$sourceColumn},
                    fn (string $candidate): bool => DB::table($table)->where('slug', $candidate)->exists(),
                );

                DB::table($table)->where('id', $row->id)->update(['slug' => $slug]);
            });
    }
};

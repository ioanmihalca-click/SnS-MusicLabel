<?php

use App\Support\Spotify\SpotifyUrl;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stage 1, step D: drops the legacy embed columns and the featured_tracks table,
 * but only once step B converted every row. Rolling back recreates them and
 * rebuilds the embed HTML from spotify_url, so the previous site keeps working.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->ensureEverythingWasConverted();

        Schema::table('releases', function (Blueprint $table) {
            $table->dropColumn('spotify_embed_code');
        });

        Schema::table('playlists', function (Blueprint $table) {
            $table->dropColumn('spotify_embed_url');
        });

        Schema::dropIfExists('featured_tracks');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('releases', function (Blueprint $table) {
            $table->text('spotify_embed_code')->nullable();
        });

        Schema::table('playlists', function (Blueprint $table) {
            $table->text('spotify_embed_url')->nullable();
        });

        Schema::create('featured_tracks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('artist_name');
            $table->string('spotify_track_url');
            $table->string('cover_image')->nullable();
            $table->date('released_at')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'order']);
        });

        $this->rebuildEmbeds('releases', 'spotify_embed_code');
        $this->rebuildEmbeds('playlists', 'spotify_embed_url');
        $this->rebuildFeaturedTracks();
    }

    /**
     * @throws RuntimeException when a row still holds legacy HTML that step B could not convert.
     */
    private function ensureEverythingWasConverted(): void
    {
        $unconvertedIds = array_filter([
            'releases' => $this->idsWithUnconvertedEmbeds('releases', 'spotify_embed_code'),
            'playlists' => $this->idsWithUnconvertedEmbeds('playlists', 'spotify_embed_url'),
            'featured_tracks' => $this->unconvertedFeaturedTrackIds(),
        ]);

        if ($unconvertedIds === []) {
            return;
        }

        $details = collect($unconvertedIds)
            ->map(fn (array $ids, string $table): string => "{$table} #".implode(', #', $ids))
            ->implode('; ');

        throw new RuntimeException(
            "Legacy Spotify embeds without a spotify_url, nothing was dropped: {$details}. "
            .'Set a valid open.spotify.com URL on these rows (or clear the embed), then run the migration again.'
        );
    }

    /**
     * @return list<int>
     */
    private function idsWithUnconvertedEmbeds(string $table, string $embedColumn): array
    {
        return DB::table($table)
            ->whereNull('spotify_url')
            ->whereNotNull($embedColumn)
            ->orderBy('id')
            ->get(['id', $embedColumn])
            ->filter(fn (object $row): bool => trim((string) $row->{$embedColumn}) !== '')
            ->map(fn (object $row): int => (int) $row->id)
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    private function unconvertedFeaturedTrackIds(): array
    {
        if (! Schema::hasTable('featured_tracks')) {
            return [];
        }

        return DB::table('featured_tracks')
            ->orderBy('id')
            ->get()
            ->reject(function (object $track): bool {
                $spotifyUrl = SpotifyUrl::parse($track->spotify_track_url);

                return $spotifyUrl !== null
                    && DB::table('releases')->where('spotify_url', $spotifyUrl->url())->exists();
            })
            ->map(fn (object $track): int => (int) $track->id)
            ->values()
            ->all();
    }

    private function rebuildEmbeds(string $table, string $embedColumn): void
    {
        DB::table($table)
            ->whereNotNull('spotify_url')
            ->lazyById()
            ->each(function (object $row) use ($table, $embedColumn): void {
                $spotifyUrl = SpotifyUrl::parse($row->spotify_url);

                if ($spotifyUrl === null) {
                    return;
                }

                DB::table($table)->where('id', $row->id)->update([$embedColumn => $this->embedCode($spotifyUrl)]);
            });
    }

    private function rebuildFeaturedTracks(): void
    {
        DB::table('releases')
            ->where('is_featured', true)
            ->whereNotNull('spotify_url')
            ->orderByDesc('released_at')
            ->orderByDesc('id')
            ->get()
            ->values()
            ->each(function (object $release, int $index): void {
                $artistName = $release->artist_display ?: DB::table('artist_release')
                    ->join('artists', 'artists.id', '=', 'artist_release.artist_id')
                    ->where('artist_release.release_id', $release->id)
                    ->orderBy('artists.name')
                    ->pluck('artists.name')
                    ->join(' & ');

                DB::table('featured_tracks')->insert([
                    'title' => $release->title,
                    'artist_name' => $artistName,
                    'spotify_track_url' => $release->spotify_url,
                    'cover_image' => $release->cover_image,
                    'released_at' => $release->released_at,
                    'order' => $index,
                    'is_active' => true,
                    'created_at' => $release->created_at,
                    'updated_at' => $release->updated_at,
                ]);
            });
    }

    private function embedCode(SpotifyUrl $spotifyUrl): string
    {
        return '<iframe style="border-radius:12px" src="'.$spotifyUrl->embedSrc().'?utm_source=generator" '
            .'width="100%" height="352" frameBorder="0" allowfullscreen="" '
            .'allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>';
    }
};

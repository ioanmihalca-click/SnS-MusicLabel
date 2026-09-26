<?php

namespace App\Console\Commands;

use App\Enums\Genre;
use App\Enums\ReleaseFormat;
use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Release;
use App\Support\Duration;
use App\Support\Slug;
use App\Support\Spotify\SpotifyUrl;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use JsonException;
use Throwable;

/**
 * One-off backfill of the structured catalogue fields from curated data
 * (database/data/catalog.json). Deliberately not part of deploy.sh: it would
 * refill fields an admin emptied on purpose.
 *
 * Without --overwrite it only fills empty fields, creates a tracklist only for
 * releases without tracks and links artists only to releases without any. The
 * one exception: a title or artist name still equal to its legacy value (e.g.
 * "Snow N Stuff - °The Change°") is replaced by the clean one, and a release
 * slug is regenerated with it.
 */
class BackfillCatalog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:backfill
        {--path= : Catalogue JSON file (defaults to database/data/catalog.json)}
        {--dry-run : Report what would change without saving anything}
        {--overwrite : Also replace filled values, titles, slugs, tracklists and artist links}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill empty release, artist and playlist fields from the curated catalogue file';

    private const SPOTIFY_ID_PATTERN = '/^[A-Za-z0-9]{22}$/';

    private const SLUG_PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = $this->option('path') ?: database_path('data/catalog.json');

        $catalog = $this->readCatalog($path);

        if ($catalog === null) {
            return self::FAILURE;
        }

        $errors = $this->validationErrors($catalog);

        if ($errors !== []) {
            $this->components->error("The catalogue [{$path}] is invalid. Nothing was changed.");

            foreach ($errors as $error) {
                $this->line("  - {$error}");
            }

            return self::FAILURE;
        }

        DB::beginTransaction();

        try {
            $this->backfillArtists($catalog['artists'] ?? []);
            $this->backfillReleases($catalog['releases']);
            $this->backfillPlaylists($catalog['playlists'] ?? []);
        } catch (Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }

        if ($this->option('dry-run')) {
            DB::rollBack();
            $this->components->warn('Dry run: nothing was saved.');
        } else {
            DB::commit();
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function readCatalog(string $path): ?array
    {
        if (! File::isFile($path)) {
            $this->components->error("Catalogue file not found: {$path}");

            return null;
        }

        try {
            $catalog = File::json($path, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->components->error("The catalogue [{$path}] is not valid JSON: {$exception->getMessage()}");

            return null;
        }

        if (! is_array($catalog)) {
            $this->components->error("The catalogue [{$path}] must be a JSON object.");

            return null;
        }

        return $catalog;
    }

    /**
     * Validate the whole file before any write.
     *
     * @param  array<string, mixed>  $catalog
     * @return list<string>
     */
    private function validationErrors(array $catalog): array
    {
        $url = ['nullable', 'string', 'url', 'max:255'];
        $text = ['nullable', 'string', 'max:255'];

        $validator = Validator::make($catalog, [
            'releases' => ['required', 'array'],
            'releases.*' => ['array'],
            'releases.*.type' => ['required', Rule::in(['album', 'track'])],
            'releases.*.legacy_title' => $text,
            'releases.*.title' => ['required', 'string', 'max:255'],
            'releases.*.artist_display' => $text,
            'releases.*.artists' => ['nullable', 'array'],
            'releases.*.artists.*' => ['string', 'regex:'.self::SLUG_PATTERN],
            'releases.*.released_at' => ['nullable', 'date_format:Y-m-d'],
            'releases.*.format' => ['nullable', Rule::enum(ReleaseFormat::class)],
            'releases.*.genre' => ['nullable', Rule::enum(Genre::class)],
            'releases.*.smartlink_url' => $url,
            'releases.*.support' => ['nullable', 'array'],
            'releases.*.support.*' => ['string', 'max:255'],
            'releases.*.chart_position' => $text,
            'releases.*.chart_name' => $text,
            'releases.*.description' => ['nullable', 'string'],
            'releases.*.tracks' => ['nullable', 'array'],
            'releases.*.tracks.*.title' => ['required', 'string', 'max:255'],
            'releases.*.tracks.*.version' => $text,
            'releases.*.tracks.*.duration' => ['nullable', 'string', 'regex:'.Duration::PATTERN],
            'releases.*.tracks.*.isrc' => ['nullable', 'string', 'max:15'],
            'artists' => ['nullable', 'array'],
            'artists.*' => ['array'],
            'artists.*.legacy_name' => $text,
            'artists.*.order' => ['nullable', 'integer'],
            'artists.*.spotify_url' => $url,
            'artists.*.description' => ['nullable', 'string'],
            'artists.*.role' => $text,
            'artists.*.origin' => $text,
            'artists.*.highlights' => ['nullable', 'array'],
            'artists.*.highlights.*' => ['string', 'max:255'],
            'artists.*.instagram_url' => $url,
            'artists.*.soundcloud_url' => $url,
            'artists.*.beatport_url' => $url,
            'playlists' => ['nullable', 'array'],
            'playlists.*' => ['array'],
            'playlists.*.order' => ['nullable', 'integer'],
            'playlists.*.title' => ['required', 'string', 'max:255'],
            'playlists.*.description' => $text,
        ]);

        $errors = $validator->errors()->all();

        foreach (['releases', 'playlists'] as $section) {
            foreach (array_keys(is_array($catalog[$section] ?? null) ? $catalog[$section] : []) as $id) {
                if (! preg_match(self::SPOTIFY_ID_PATTERN, (string) $id)) {
                    $errors[] = "{$section}: [{$id}] is not a Spotify ID.";
                }
            }
        }

        return $errors;
    }

    /**
     * @param  array<string, array<string, mixed>>  $artists  keyed by display name
     */
    private function backfillArtists(array $artists): void
    {
        $missing = [];
        $matched = 0;

        foreach ($artists as $displayName => $data) {
            $slug = Slug::from((string) $displayName);
            $artist = Artist::query()->where('slug', $slug)->first();

            if ($artist === null) {
                $missing[] = $slug;

                continue;
            }

            $matched++;

            if ($this->shouldReplaceLegacy($artist->name, $data['legacy_name'] ?? null)) {
                $artist->name = (string) $displayName;
            }

            $this->fillBlank($artist, [
                'spotify_url' => $data['spotify_url'] ?? null,
                'description' => $data['description'] ?? null,
                'role' => $data['role'] ?? null,
                'origin' => $data['origin'] ?? null,
                'highlights' => $data['highlights'] ?? null,
                'instagram_url' => $data['instagram_url'] ?? null,
                'soundcloud_url' => $data['soundcloud_url'] ?? null,
                'beatport_url' => $data['beatport_url'] ?? null,
            ]);

            if ($this->option('overwrite') && isset($data['order'])) {
                $artist->order = $data['order'];
            }

            $this->saveAndReport($artist, "artist {$slug}");
        }

        $this->reportSection('Artists', $matched, $missing);
    }

    /**
     * @param  array<string, array<string, mixed>>  $releases  keyed by Spotify ID
     */
    private function backfillReleases(array $releases): void
    {
        $missing = [];
        $matched = 0;

        foreach ($releases as $spotifyId => $data) {
            $spotifyUrl = SpotifyUrl::fromParts($data['type'], (string) $spotifyId);
            $release = Release::query()->where('spotify_url', $spotifyUrl->url())->first();

            if ($release === null) {
                $missing[] = "{$spotifyUrl->type}/{$spotifyId} ({$data['title']})";

                continue;
            }

            $matched++;

            if ($this->shouldReplaceLegacy($release->title, $data['legacy_title'] ?? null) && $release->title !== $data['title']) {
                $release->title = $data['title'];
            }

            if ($release->isDirty('title') || $this->option('overwrite')) {
                $release->slug = Slug::unique(
                    $release->title,
                    fn (string $slug): bool => Release::query()->where('slug', $slug)->whereKeyNot($release->getKey())->exists(),
                );
            }

            $this->fillBlank($release, [
                'released_at' => $data['released_at'] ?? null,
                'format' => $data['format'] ?? null,
                'genre' => $data['genre'] ?? null,
                'artist_display' => $data['artist_display'] ?? null,
                'smartlink_url' => $data['smartlink_url'] ?? null,
                'support' => $data['support'] ?? null,
                'chart_position' => $data['chart_position'] ?? null,
                'chart_name' => $data['chart_name'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            $label = "release {$spotifyId} ({$data['title']})";
            $changes = array_keys($release->getDirty());
            $release->save();

            $changes = [
                ...$changes,
                ...$this->backfillTracks($release, $data['tracks'] ?? []),
                ...$this->linkArtists($release, $data['artists'] ?? [], $label),
            ];

            $this->reportChanges($label, $changes);
        }

        $this->reportSection('Releases', $matched, $missing);
    }

    /**
     * @param  list<array<string, mixed>>  $tracks
     * @return list<string> the change, if any
     */
    private function backfillTracks(Release $release, array $tracks): array
    {
        if ($tracks === []) {
            return [];
        }

        $rows = array_map(fn (array $track, int $index): array => [
            'position' => $index + 1,
            'title' => $track['title'],
            'version' => $track['version'] ?? null,
            'duration_seconds' => filled($track['duration'] ?? null) ? Duration::toSeconds($track['duration']) : null,
            'isrc' => $track['isrc'] ?? null,
        ], $tracks, array_keys($tracks));

        $existingRows = $release->tracks()
            ->get(['position', 'title', 'version', 'duration_seconds', 'isrc'])
            ->map(fn (Model $track): array => $track->only(['position', 'title', 'version', 'duration_seconds', 'isrc']))
            ->all();

        if ($existingRows === $rows || ($existingRows !== [] && ! $this->option('overwrite'))) {
            return [];
        }

        $release->tracks()->delete();
        $release->tracks()->createMany($rows);

        return ['tracks ('.count($rows).')'];
    }

    /**
     * @param  list<string>  $slugs  roster artist slugs
     * @return list<string> the change, if any
     */
    private function linkArtists(Release $release, array $slugs, string $label): array
    {
        if ($slugs === []) {
            return [];
        }

        $artistIds = Artist::query()->whereIn('slug', $slugs)->pluck('id', 'slug');

        foreach (array_diff($slugs, $artistIds->keys()->all()) as $missingSlug) {
            $this->components->warn("{$label}: artist [{$missingSlug}] not found.");
        }

        $wantedIds = $artistIds->values()->sort()->values()->all();
        $currentIds = $release->artists()->pluck('artists.id')->sort()->values()->all();

        if ($wantedIds === [] || $wantedIds === $currentIds || ($currentIds !== [] && ! $this->option('overwrite'))) {
            return [];
        }

        $release->artists()->sync($wantedIds);

        return ['artists ('.count($wantedIds).')'];
    }

    /**
     * @param  array<string, array<string, mixed>>  $playlists  keyed by Spotify ID
     */
    private function backfillPlaylists(array $playlists): void
    {
        $missing = [];
        $matched = 0;

        foreach ($playlists as $spotifyId => $data) {
            $spotifyUrl = SpotifyUrl::fromParts('playlist', (string) $spotifyId);
            $playlist = Playlist::query()->where('spotify_url', $spotifyUrl->url())->first();

            if ($playlist === null) {
                $missing[] = "playlist/{$spotifyId} ({$data['title']})";

                continue;
            }

            $matched++;

            $this->fillBlank($playlist, [
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
            ]);

            if ($this->option('overwrite') && isset($data['order'])) {
                $playlist->order = $data['order'];
            }

            $this->saveAndReport($playlist, "playlist {$spotifyId} ({$data['title']})");
        }

        $this->reportSection('Playlists', $matched, $missing);
    }

    /**
     * Legacy titles and names (as typed on the old site) are replaced by the curated
     * ones, unless an admin already edited them.
     */
    private function shouldReplaceLegacy(?string $current, ?string $legacy): bool
    {
        return $this->option('overwrite') || ($legacy !== null && $current === $legacy);
    }

    /**
     * Set each non-empty catalogue value on the model, but only over empty
     * attributes unless --overwrite is given.
     *
     * @param  array<string, mixed>  $values
     */
    private function fillBlank(Model $model, array $values): void
    {
        foreach ($values as $attribute => $value) {
            if (blank($value)) {
                continue;
            }

            if ($this->option('overwrite') || blank($model->getAttribute($attribute))) {
                $model->setAttribute($attribute, $value);
            }
        }
    }

    private function saveAndReport(Model $model, string $label): void
    {
        $changes = array_keys($model->getDirty());
        $model->save();

        $this->reportChanges($label, $changes);
    }

    /**
     * @param  list<string>  $changes
     */
    private function reportChanges(string $label, array $changes): void
    {
        if ($changes === []) {
            return;
        }

        $this->components->twoColumnDetail($label, implode(', ', $changes));
    }

    /**
     * @param  list<string>  $missing
     */
    private function reportSection(string $section, int $matched, array $missing): void
    {
        $this->components->info("{$section}: {$matched} matched, ".count($missing).' not found.');

        foreach ($missing as $item) {
            $this->components->warn("Not found: {$item}");
        }
    }
}

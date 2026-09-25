<?php

namespace App\Support\Seo;

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\PlaylistsController;
use App\Livewire\BlogIndex;
use App\Livewire\ReleaseCatalogue;
use App\Models\Artist;
use App\Models\Blog;
use App\Models\Photo;
use App\Models\Playlist;
use App\Models\Release;
use App\Models\Track;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The single list of the site's public, indexable pages, shared by sitemap.xml,
 * llms.txt and IndexNow. A new public page type is added here once.
 */
final class PublicPages
{
    public const SECTION_PAGES = 'Pages';

    public const SECTION_RELEASES = 'Releases';

    public const SECTION_ARTISTS = 'Artists';

    public const SECTION_BLOG = 'Blog';

    /**
     * Every public page, homepage first.
     *
     * @return Collection<int, PublicPage>
     */
    public function all(): Collection
    {
        $releases = Release::query()->with('artists')->newestFirst()->get();
        $artists = Artist::query()->with('releases')->inRosterOrder()->get();
        $playlists = Playlist::query()->active()->get();
        $photos = Photo::query()->get();
        $posts = Blog::query()->published()->latest('published_at')->get();

        return collect([
            $this->home(),
            $this->about($photos),
            $this->playlists($playlists),
            $this->releaseCatalogue($releases),
        ])
            ->concat($releases->map(fn (Release $release): PublicPage => $this->release($release)))
            ->push($this->artistIndex($artists))
            ->concat($artists->map(fn (Artist $artist): PublicPage => $this->artist($artist)))
            ->push($this->blogIndex($posts))
            ->concat($posts->map(fn (Blog $post): PublicPage => $this->blogPost($post)));
    }

    /**
     * The absolute URLs whose content depends on the model, to resubmit to
     * search engines after it is saved or deleted.
     *
     * @return list<string>
     */
    public function urlsAffectedBy(Model $model): array
    {
        $paths = match (true) {
            $model instanceof Blog => $this->blogPaths($model),
            $model instanceof Release => [
                '/',
                route('releases.index', absolute: false),
                ...$this->slugPaths($model, 'releases.show'),
                ...$model->artists()->pluck('slug')->map(fn (string $slug): string => route('artists.show', $slug, absolute: false)),
            ],
            $model instanceof Track => $model->release === null ? [] : $this->slugPaths($model->release, 'releases.show'),
            $model instanceof Artist => [
                '/',
                route('artists.index', absolute: false),
                ...$this->slugPaths($model, 'artists.show'),
                route('releases.index', absolute: false),
            ],
            $model instanceof Playlist => ['/', route('playlists.index', absolute: false)],
            $model instanceof Photo => ['/', route('about', absolute: false)],
            default => [],
        };

        return array_values(array_unique(array_map(PublicUrl::to(...), $paths)));
    }

    private function home(): PublicPage
    {
        return new PublicPage(
            path: '/',
            title: 'Home',
            description: SeoData::DEFAULT_DESCRIPTION,
            section: self::SECTION_PAGES,
            lastModifiedAt: $this->lastUpdatedAt(Release::class, Artist::class, Playlist::class, Photo::class),
        );
    }

    /**
     * @param  Collection<int, Photo>  $photos
     */
    private function about(Collection $photos): PublicPage
    {
        return new PublicPage(
            path: route('about', absolute: false),
            title: 'About',
            description: AboutController::DESCRIPTION,
            section: self::SECTION_PAGES,
            lastModifiedAt: $this->latest($photos),
            images: $photos->map(fn (Photo $photo): string => $photo->imageUrl())->all(),
        );
    }

    /**
     * @param  Collection<int, Playlist>  $playlists
     */
    private function playlists(Collection $playlists): PublicPage
    {
        return new PublicPage(
            path: route('playlists.index', absolute: false),
            title: 'Playlists',
            description: PlaylistsController::DESCRIPTION,
            section: self::SECTION_PAGES,
            lastModifiedAt: $this->latest($playlists),
            images: $playlists->map(fn (Playlist $playlist): ?string => $playlist->cachedArtworkUrl())->filter()->values()->all(),
        );
    }

    /**
     * @param  Collection<int, Release>  $releases
     */
    private function releaseCatalogue(Collection $releases): PublicPage
    {
        return new PublicPage(
            path: route('releases.index', absolute: false),
            title: 'Releases',
            description: ReleaseCatalogue::DESCRIPTION,
            section: self::SECTION_RELEASES,
            lastModifiedAt: $this->latest($releases),
        );
    }

    private function release(Release $release): PublicPage
    {
        return new PublicPage(
            path: route('releases.show', $release->slug, absolute: false),
            title: filled($release->credit) ? "{$release->title} by {$release->credit}" : $release->title,
            description: $release->summary(),
            section: self::SECTION_RELEASES,
            lastModifiedAt: $release->updated_at,
            images: array_values(array_filter([$release->cachedArtworkUrl()])),
        );
    }

    /**
     * @param  Collection<int, Artist>  $artists
     */
    private function artistIndex(Collection $artists): PublicPage
    {
        return new PublicPage(
            path: route('artists.index', absolute: false),
            title: 'Artists',
            description: ArtistController::DESCRIPTION,
            section: self::SECTION_ARTISTS,
            lastModifiedAt: $this->latest($artists),
        );
    }

    private function artist(Artist $artist): PublicPage
    {
        return new PublicPage(
            path: route('artists.show', $artist->slug, absolute: false),
            title: $artist->name,
            description: $artist->summary(),
            section: self::SECTION_ARTISTS,
            lastModifiedAt: $this->latest(collect([$artist])->concat($artist->releases)),
            images: array_values(array_filter([$artist->cachedArtworkUrl()])),
        );
    }

    /**
     * @param  Collection<int, Blog>  $posts
     */
    private function blogIndex(Collection $posts): PublicPage
    {
        return new PublicPage(
            path: route('blog.index', absolute: false),
            title: 'Blog',
            description: BlogIndex::DESCRIPTION,
            section: self::SECTION_BLOG,
            lastModifiedAt: $posts->map(fn (Blog $post): CarbonInterface => $post->lastModifiedAt())->max(),
        );
    }

    private function blogPost(Blog $post): PublicPage
    {
        return new PublicPage(
            path: route('blog.show', $post->slug, absolute: false),
            title: $post->title,
            description: $post->summary(),
            section: self::SECTION_BLOG,
            lastModifiedAt: $post->lastModifiedAt(),
            images: array_values(array_filter([$post->coverUrl()])),
        );
    }

    /**
     * The most recent `updated_at` of the given models.
     *
     * @param  Collection<int, Model>  $models
     */
    private function latest(Collection $models): ?CarbonInterface
    {
        return $models->map(fn (Model $model): ?CarbonInterface => $model->updated_at)->filter()->max();
    }

    /**
     * The most recent `updated_at` across whole tables.
     *
     * @param  class-string<Model>  ...$models
     */
    private function lastUpdatedAt(string ...$models): ?CarbonInterface
    {
        return collect($models)
            ->map(fn (string $model): mixed => $model::query()->max('updated_at'))
            ->filter()
            ->map(fn (mixed $updatedAt): CarbonInterface => Carbon::parse($updatedAt))
            ->max();
    }

    /**
     * The model's page, plus its previous URL when the slug changed.
     *
     * @return list<string>
     */
    private function slugPaths(Release|Artist $model, string $routeName): array
    {
        $slugs = array_unique(array_filter([$model->slug, $model->getOriginal('slug')]));

        return array_values(array_map(fn (string $slug): string => route($routeName, $slug, absolute: false), $slugs));
    }

    /**
     * The blog listing and the post's URL, plus its previous URL when the slug
     * changed, as long as the post is or was public.
     *
     * @return list<string>
     */
    private function blogPaths(Blog $post): array
    {
        $previousPublishedAt = $post->getOriginal('published_at');
        $wasPublished = $previousPublishedAt instanceof CarbonInterface && $previousPublishedAt->lte(now());

        if (! $post->isPublished() && ! $wasPublished) {
            return [];
        }

        $slugs = array_unique(array_filter([$post->slug, $post->getOriginal('slug')]));

        return [
            route('blog.index', absolute: false),
            ...array_map(fn (string $slug): string => route('blog.show', $slug, absolute: false), $slugs),
        ];
    }
}

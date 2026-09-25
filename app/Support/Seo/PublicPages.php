<?php

namespace App\Support\Seo;

use App\Livewire\BlogIndex;
use App\Models\Artist;
use App\Models\Blog;
use App\Models\Photo;
use App\Models\Playlist;
use App\Models\Release;
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

    public const SECTION_BLOG = 'Blog';

    /**
     * Every public page, homepage first.
     *
     * @return Collection<int, PublicPage>
     */
    public function all(): Collection
    {
        $posts = Blog::query()->published()->latest('published_at')->get();

        return collect([$this->home(), $this->blogIndex($posts)])
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
            $model instanceof Release, $model instanceof Artist, $model instanceof Playlist, $model instanceof Photo => ['/'],
            default => [],
        };

        return array_values(array_unique(array_map(PublicUrl::to(...), $paths)));
    }

    private function home(): PublicPage
    {
        $lastModifiedAt = collect([Release::class, Artist::class, Playlist::class, Photo::class])
            ->map(fn (string $model): mixed => $model::query()->max('updated_at'))
            ->filter()
            ->map(fn (mixed $updatedAt): CarbonInterface => Carbon::parse($updatedAt))
            ->max();

        return new PublicPage(
            path: '/',
            title: 'Home',
            description: SeoData::DEFAULT_DESCRIPTION,
            section: self::SECTION_PAGES,
            lastModifiedAt: $lastModifiedAt,
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

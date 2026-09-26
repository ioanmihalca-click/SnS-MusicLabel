<?php

namespace App\Support;

use App\Models\Blog;
use App\Models\Release;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * One entry of a news feed: a blog post, or a release that is out. Releases
 * appear in the homepage feed automatically, so it stays current without
 * writing a post for each one; /blog lists posts only.
 *
 * `playUri`, `playTitle` and `playCredit` feed the entry's Play button
 * (x-play-button), shown only with a URI: null for posts and for releases
 * Spotify cannot play.
 */
final class NewsItem
{
    public const TYPE_RELEASE = 'release';

    public const TYPE_POST = 'post';

    public function __construct(
        public readonly string $type,
        public readonly int $id,
        public readonly string $title,
        public readonly string $url,
        public readonly CarbonInterface $date,
        public readonly ?string $imageUrl = null,
        public readonly ?string $summary = null,
        public readonly ?string $playUri = null,
        public readonly ?string $playTitle = null,
        public readonly ?string $playCredit = null,
    ) {}

    /**
     * A release that is out. Expects the `artists` relation to be loaded.
     */
    public static function fromRelease(Release $release): self
    {
        $title = filled($release->credit)
            ? "{$release->title} by {$release->credit} is out now"
            : "{$release->title} is out now";

        return new self(
            type: self::TYPE_RELEASE,
            id: $release->id,
            title: $title,
            url: route('releases.show', $release->slug),
            date: $release->released_at,
            imageUrl: $release->coverUrl(),
            playUri: $release->playUri(),
            playTitle: $release->title,
            playCredit: filled($release->credit) ? $release->credit : null,
        );
    }

    public static function fromPost(Blog $post): self
    {
        return new self(
            type: self::TYPE_POST,
            id: $post->id,
            title: $post->title,
            url: route('blog.show', $post->slug),
            date: $post->published_at,
            imageUrl: $post->coverUrl(),
            summary: $post->summary(),
        );
    }

    /**
     * The homepage feed: up to half releases already out and half published
     * posts; when one kind runs short, the other fills the free places. Newest
     * first, the most recently added first on the same date.
     *
     * @return Collection<int, self>
     */
    public static function latest(int $limit = 4): Collection
    {
        $releases = Release::query()
            ->with('artists')
            ->whereDate('released_at', '<=', today())
            ->newestFirst()
            ->limit($limit)
            ->get()
            ->map(fn (Release $release): self => self::fromRelease($release));

        $posts = Blog::query()
            ->published()
            ->latest('published_at')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (Blog $post): self => self::fromPost($post));

        $releaseCount = min($releases->count(), max(intdiv($limit, 2), $limit - $posts->count()));
        $postCount = min($posts->count(), $limit - $releaseCount);

        return $releases->take($releaseCount)
            ->concat($posts->take($postCount))
            ->sortBy([
                fn (self $a, self $b): int => $b->date->getTimestamp() <=> $a->date->getTimestamp(),
                fn (self $a, self $b): int => $b->id <=> $a->id,
            ])
            ->values();
    }

    /**
     * The label shown above the title.
     */
    public function label(): string
    {
        return $this->type === self::TYPE_RELEASE ? 'Release' : 'News';
    }
}

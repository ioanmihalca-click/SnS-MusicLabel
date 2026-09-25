<?php

namespace App\Livewire;

use App\Models\Blog;
use App\Support\ArticleHtml;
use App\Support\Seo\PublicUrl;
use App\Support\Seo\Schema;
use App\Support\Seo\SeoData;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * A published post: its cleaned body (App\Support\ArticleHtml), share links
 * and the three newest other posts.
 */
#[Layout('components.layouts.site')]
class BlogShow extends Component
{
    public const RELATED_POSTS = 3;

    public Blog $blog;

    /**
     * @var Collection<int, Blog>
     */
    public Collection $relatedArticles;

    public function mount(string $slug): void
    {
        $this->blog = Blog::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $this->relatedArticles = Blog::query()
            ->published()
            ->whereKeyNot($this->blog->id)
            ->latest('published_at')
            ->latest('id')
            ->limit(self::RELATED_POSTS)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.blog-show', [
            'body' => ArticleHtml::render($this->blog->content),
            'trail' => $this->trail(),
            'shareUrl' => PublicUrl::to($this->path()),
        ])->layoutData([
            'seo' => $this->seo(),
        ]);
    }

    private function path(): string
    {
        return route('blog.show', $this->blog->slug, absolute: false);
    }

    /**
     * @return array<string, string>
     */
    private function trail(): array
    {
        return [
            '/' => 'Home',
            route('blog.index', absolute: false) => 'News',
            $this->path() => $this->blog->title,
        ];
    }

    private function seo(): SeoData
    {
        return new SeoData(
            title: $this->blog->meta_title ?: $this->blog->title,
            description: $this->blog->summary(),
            path: $this->path(),
            image: $this->blog->coverUrl(),
            type: 'article',
            schema: [
                Schema::breadcrumbs($this->trail()),
                Schema::blogPosting($this->blog),
            ],
        );
    }
}

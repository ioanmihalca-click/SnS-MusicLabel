<?php

namespace App\Livewire;

use App\Models\Blog;
use App\Support\Seo\Schema;
use App\Support\Seo\SeoData;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class BlogShow extends Component
{
    public $blog;

    public $relatedArticles;

    public function mount($slug)
    {
        $this->blog = Blog::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $this->relatedArticles = Blog::query()
            ->published()
            ->whereKeyNot($this->blog->id)
            ->inRandomOrder()
            ->limit(3)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.blog-show')
            ->layout('components.layouts.blog')
            ->layoutData([
                'seo' => $this->seo(),
            ]);
    }

    private function seo(): SeoData
    {
        $path = route('blog.show', $this->blog->slug, absolute: false);

        return new SeoData(
            title: $this->blog->meta_title ?: $this->blog->title,
            description: $this->blog->summary(),
            path: $path,
            image: $this->blog->coverUrl(),
            type: 'article',
            schema: [
                Schema::breadcrumbs([
                    '/' => 'Home',
                    route('blog.index', absolute: false) => 'Blog',
                    $path => $this->blog->title,
                ]),
                Schema::blogPosting($this->blog),
            ],
        );
    }
}

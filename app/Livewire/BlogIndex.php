<?php

namespace App\Livewire;

use App\Models\Blog;
use App\Support\Seo\Schema;
use App\Support\Seo\SeoData;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The News listing at /blog: published posts only, newest first, the first
 * one shown large. The search field is hidden for now, but ?search= still
 * filters (and keeps the results out of the index).
 */
#[Layout('components.layouts.site')]
class BlogIndex extends Component
{
    use WithPagination;

    public const DESCRIPTION = "News and insights from the Snow 'n' Stuff label: releases, artists, music production and the electronic music industry.";

    #[Url(except: '')]
    public string $search = '';

    public int $postsPerPage = 10;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $blogs = Blog::query()
            ->published()
            ->when($this->search, function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('content', 'like', '%'.$this->search.'%');
                });
            })
            ->latest('published_at')
            ->latest('id')
            ->paginate($this->postsPerPage);

        return view('livewire.blog-index', [
            'blogs' => $blogs,
            'trail' => $this->trail(),
        ])->layoutData([
            'seo' => $this->seo(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function trail(): array
    {
        return ['/' => 'Home', route('blog.index', absolute: false) => 'News'];
    }

    /**
     * Search results are kept out of the index; the listing itself is canonical at /blog.
     */
    private function seo(): SeoData
    {
        return new SeoData(
            title: 'News - '.SeoData::SITE_NAME,
            description: self::DESCRIPTION,
            path: route('blog.index', absolute: false),
            noindex: $this->search !== '',
            schema: [
                Schema::breadcrumbs($this->trail()),
            ],
        );
    }
}

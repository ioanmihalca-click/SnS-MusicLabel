<?php

namespace App\Livewire;

use App\Models\Blog;
use App\Support\Seo\Schema;
use App\Support\Seo\SeoData;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.blog')]
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
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('content', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('published_at', 'desc')
            ->paginate($this->postsPerPage);

        return view('livewire.blog-index', [
            'blogs' => $blogs,
        ])->layoutData([
            'seo' => $this->seo(),
        ]);
    }

    /**
     * Search results are kept out of the index; the listing itself is canonical at /blog.
     */
    private function seo(): SeoData
    {
        $path = route('blog.index', absolute: false);

        return new SeoData(
            title: "Blog - Snow 'n' Stuff",
            description: self::DESCRIPTION,
            path: $path,
            noindex: $this->search !== '',
            schema: [
                Schema::breadcrumbs(['/' => 'Home', $path => 'Blog']),
            ],
        );
    }
}

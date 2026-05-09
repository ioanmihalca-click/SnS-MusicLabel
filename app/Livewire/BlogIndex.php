<?php

namespace App\Livewire;

use App\Models\Blog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.blog')]
#[Title('Our Blog - Latest Posts')]
class BlogIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public int $postsPerPage = 10;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $blogs = Blog::query()
            ->where('published_at', '<=', now())
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
            'meta_title' => 'Our Blog - Latest Posts',
            'meta_description' => 'Read our latest blog posts on various topics.',
            'meta_keywords' => 'blog, articles, news',
        ]);
    }
}

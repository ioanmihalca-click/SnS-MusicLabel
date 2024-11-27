<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Blog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.blog')]
#[Title('Our Blog - Latest Posts')]
class BlogIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $postsPerPage = 10;
    public $debugSql = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Blog::where('published_at', '<=', now())
            ->when($this->search, function ($query) {
                return $query->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('content', 'like', '%' . $this->search . '%');
            })
            ->orderBy('published_at', 'desc');

     
        
        $blogs = $query->paginate($this->postsPerPage);

        return view('livewire.blog-index', [
            'blogs' => $blogs,
        ])
        ->layoutData([
            'meta_title' => 'Our Blog - Latest Posts',
            'meta_description' => 'Read our latest blog posts on various topics.',
            'meta_keywords' => 'blog, articles, news',
        ]);
    }
}
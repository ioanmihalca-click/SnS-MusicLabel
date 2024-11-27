<?php

namespace App\Livewire;

use App\Models\Blog;
use Livewire\Component;

class BlogShow extends Component
{
    public $blog;
    public $relatedArticles;

    public function mount($slug)
    {
        $this->blog = Blog::where('slug', $slug)
            ->where('published_at', '<=', now())
            ->firstOrFail();

        $this->relatedArticles = Blog::where('id', '!=', $this->blog->id)
            ->where('published_at', '<=', now())
            ->inRandomOrder()
            ->limit(3)
            ->get();
    }

    public function render()
    {
        return view('livewire.blog-show')
            ->layout('components.layouts.blog')
            ->layoutData([
                'meta_title' => $this->blog->meta_title ?? $this->blog->title,
                'meta_description' => $this->blog->meta_description,
                'meta_keywords' => $this->blog->meta_keywords,
                'og_image' => $this->blog->cover_image ? asset('storage/' . $this->blog->cover_image) : null,
            ]);
    }
}
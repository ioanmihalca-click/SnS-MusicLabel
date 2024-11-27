<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Blog;

class LatestArticles extends Component
{
    public function render()
    {
        $latestArticles = Blog::where('published_at', '<=', now())
                              ->orderBy('published_at', 'desc')
                              ->take(3)
                              ->get();

        return view('livewire.latest-articles', [
            'latestArticles' => $latestArticles
        ]);
    }
}
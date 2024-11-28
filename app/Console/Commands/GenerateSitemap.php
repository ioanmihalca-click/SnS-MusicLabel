<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;
use App\Models\Blog;
use Carbon\Carbon;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap.';

    public function handle()
    {
        // Creăm sitemap-ul manual în loc să folosim generatorul automat
        $sitemap = app()->make(\Spatie\Sitemap\Sitemap::class);

        // Adăugăm pagina principală
        $sitemap->add(Url::create('/')
            ->setLastModificationDate(now())
            ->setPriority(1.00));

        // Adăugăm pagina de blog
        $sitemap->add(Url::create('/blog')
            ->setLastModificationDate($this->getLastBlogUpdate())
            ->setPriority(0.90));

        // Adăugăm articolele de blog publicate
        Blog::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get()
            ->each(function (Blog $post) use ($sitemap) {
                $sitemap->add(Url::create("/blog/{$post->slug}")
                    ->setLastModificationDate($post->updated_at)
                    ->setPriority(0.80));
            });

        // Salvăm sitemap-ul
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully!');
    }

    private function getLastBlogUpdate(): Carbon
    {
        $lastPost = Blog::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('updated_at')
            ->first();

        return $lastPost ? $lastPost->updated_at : now();
    }
}
<?php

namespace App\Http\Controllers;

use App\Support\Seo\PageCache;
use App\Support\Seo\PublicPage;
use App\Support\Seo\PublicPages;
use Illuminate\Http\Response;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    /**
     * sitemap.xml, generated from the public pages and cached in the `pages` store.
     */
    public function __invoke(PublicPages $publicPages): Response
    {
        $xml = PageCache::remember('sitemap.xml', fn (): string => Sitemap::create()
            ->add($publicPages->all()->map(fn (PublicPage $page): Url => $this->tag($page)))
            ->render());

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    private function tag(PublicPage $page): Url
    {
        $tag = Url::create($page->url());

        if ($page->lastModifiedAt !== null) {
            $tag->setLastModificationDate($page->lastModifiedAt);
        }

        foreach ($page->images as $image) {
            $tag->addImage($image);
        }

        return $tag;
    }
}

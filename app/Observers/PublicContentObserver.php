<?php

namespace App\Observers;

use App\Support\Seo\IndexNow;
use App\Support\Seo\PageCache;
use App\Support\Seo\PublicPages;
use Illuminate\Database\Eloquent\Model;

/**
 * Keeps the public documents fresh when content shown on the site changes:
 * the `pages` cache (sitemap, llms.txt, Markdown versions) is flushed and the
 * affected URLs are resubmitted through IndexNow.
 */
class PublicContentObserver
{
    public function __construct(
        private readonly IndexNow $indexNow,
        private readonly PublicPages $publicPages,
    ) {}

    public function saved(Model $model): void
    {
        $this->publicContentChanged($model);
    }

    public function deleted(Model $model): void
    {
        $this->publicContentChanged($model);
    }

    private function publicContentChanged(Model $model): void
    {
        PageCache::flush();

        if ($this->indexNow->isEnabled()) {
            $this->indexNow->submit(...$this->publicPages->urlsAffectedBy($model));
        }
    }
}

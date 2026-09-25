<?php

namespace App\Support\Seo;

use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * The dedicated `pages` cache store, which holds the generated public documents:
 * sitemap.xml, llms.txt and the Markdown versions of pages.
 *
 * It is flushed whenever public content is saved or deleted. The one-hour TTL
 * catches the changes that fire no model events: scheduled posts going live
 * and rows reordered in Filament.
 */
final class PageCache
{
    public const STORE = 'pages';

    public const TTL_SECONDS = 3600;

    /**
     * @template TValue
     *
     * @param  Closure(): TValue  $callback
     * @return TValue
     */
    public static function remember(string $key, Closure $callback): mixed
    {
        return self::store()->remember($key, self::TTL_SECONDS, $callback);
    }

    public static function flush(): void
    {
        self::store()->clear();
    }

    private static function store(): Repository
    {
        return Cache::store(self::STORE);
    }
}

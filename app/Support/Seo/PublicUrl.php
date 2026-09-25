<?php

namespace App\Support\Seo;

/**
 * Absolute public URLs built from `app.url`, never from the incoming request,
 * so canonicals, the sitemap, llms.txt and IndexNow always name the same
 * host (no `www`, no query string).
 */
final class PublicUrl
{
    /**
     * The absolute URL of a path, e.g. "/blog" becomes "https://snow-n-stuff.com/blog"
     * and "/" becomes "https://snow-n-stuff.com/".
     */
    public static function to(string $path = '/'): string
    {
        $path = (string) parse_url($path, PHP_URL_PATH);

        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }

    /**
     * The Markdown version of a page: the path plus ".md", and "/index.md" for the homepage.
     */
    public static function markdown(string $path = '/'): string
    {
        $path = rtrim((string) parse_url($path, PHP_URL_PATH), '/');

        return self::to(($path === '' ? '/index' : $path).'.md');
    }
}

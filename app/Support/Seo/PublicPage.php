<?php

namespace App\Support\Seo;

use Carbon\CarbonInterface;

/**
 * One public, indexable page, as listed in sitemap.xml and llms.txt.
 */
final class PublicPage
{
    /**
     * @param  string  $section  The llms.txt heading the page is listed under.
     * @param  list<string>  $images  Absolute URLs of images already on our server or in cache.
     * @param  float|null  $priority  The sitemap priority, from 0 to 1; left out (0.5 for search engines) when null.
     */
    public function __construct(
        public readonly string $path,
        public readonly string $title,
        public readonly string $description,
        public readonly string $section,
        public readonly ?CarbonInterface $lastModifiedAt = null,
        public readonly array $images = [],
        public readonly ?float $priority = null,
    ) {}

    public function url(): string
    {
        return PublicUrl::to($this->path);
    }

    public function markdownUrl(): string
    {
        return PublicUrl::markdown($this->path);
    }
}

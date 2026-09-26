<?php

namespace App\Support\Seo;

/**
 * Everything the `<x-seo>` component prints in a page's `<head>`: title,
 * description, canonical, Open Graph / Twitter cards, the Markdown alternate
 * and the JSON-LD graph.
 */
final class SeoData
{
    public const SITE_NAME = "Snow 'n' Stuff";

    public const DEFAULT_TITLE = "Snow 'n' Stuff - Music Management, Label and Music Production";

    public const DEFAULT_DESCRIPTION = "Snow 'n' Stuff is an innovative music label specializing in Tech House, Deep House, House, and Techno. Discover exceptional artists and immersive live events curated by industry veterans.";

    public const DEFAULT_IMAGE_PATH = 'assets/img/og-default.jpg';

    /**
     * @param  string  $path  The page's path; the canonical is `app.url` plus this path, without a query string.
     * @param  string|null  $image  An absolute image URL; the site's 1200x630 share image when null.
     * @param  bool  $imageIsThumbnail  A small square image (e.g. Spotify's 300px thumbnail) gets the compact Twitter card.
     * @param  bool  $noindex  Keep the page out of search results (it then gets no canonical nor Markdown alternate).
     * @param  list<array<string, mixed>>  $schema  The page's own JSON-LD nodes; the label and the website are always included.
     */
    public function __construct(
        public readonly string $title = self::DEFAULT_TITLE,
        public readonly string $description = self::DEFAULT_DESCRIPTION,
        public readonly string $path = '/',
        public readonly ?string $image = null,
        public readonly bool $imageIsThumbnail = false,
        public readonly string $type = 'website',
        public readonly bool $noindex = false,
        public readonly array $schema = [],
    ) {}

    public function canonicalUrl(): string
    {
        return PublicUrl::to($this->path);
    }

    public function markdownUrl(): string
    {
        return PublicUrl::markdown($this->path);
    }

    public function imageUrl(): string
    {
        return $this->image ?? asset(self::DEFAULT_IMAGE_PATH);
    }

    public function twitterCard(): string
    {
        return $this->image !== null && $this->imageIsThumbnail ? 'summary' : 'summary_large_image';
    }

    /**
     * The JSON-LD document, safe to print inside a `<script>` element.
     */
    public function jsonLd(): string
    {
        return Schema::graph($this->schema);
    }
}

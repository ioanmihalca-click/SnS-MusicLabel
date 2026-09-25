<?php

namespace App\Support\Seo;

use App\Models\Blog;

/**
 * schema.org JSON-LD nodes. Every page prints a single `@graph`: the label and
 * the website first, then the page's own nodes, which refer back to the label
 * through its stable `@id`.
 */
final class Schema
{
    public const LOGO_PATH = 'assets/favicon/web-app-manifest-512x512.png';

    /**
     * @var list<string>
     */
    public const SAME_AS = [
        'https://www.instagram.com/snow_n_stuff',
        'https://www.facebook.com/SnowNStuff',
        'https://x.com/G_n_S_',
    ];

    /**
     * @var list<string>
     */
    public const FOUNDERS = ['Glenn Forrestgate', 'Style da Kid'];

    private const JSON_FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_THROW_ON_ERROR;

    public static function organizationId(): string
    {
        return PublicUrl::to('/').'#organization';
    }

    public static function websiteId(): string
    {
        return PublicUrl::to('/').'#website';
    }

    /**
     * The label itself.
     *
     * @return array<string, mixed>
     */
    public static function organization(): array
    {
        return [
            '@type' => 'Organization',
            '@id' => self::organizationId(),
            'name' => SeoData::SITE_NAME,
            'url' => PublicUrl::to('/'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset(self::LOGO_PATH),
                'width' => 512,
                'height' => 512,
            ],
            'description' => SeoData::DEFAULT_DESCRIPTION,
            'foundingDate' => '2020',
            'founder' => array_map(
                fn (string $name): array => ['@type' => 'Person', 'name' => $name],
                self::FOUNDERS,
            ),
            'sameAs' => self::SAME_AS,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => self::websiteId(),
            'name' => SeoData::SITE_NAME,
            'url' => PublicUrl::to('/'),
            'inLanguage' => 'en',
            'publisher' => ['@id' => self::organizationId()],
        ];
    }

    /**
     * The trail from the homepage to the current page, e.g. Home > Blog > Post.
     *
     * @param  array<string, string>  $trail  Item names keyed by path, starting with the homepage.
     * @return array<string, mixed>
     */
    public static function breadcrumbs(array $trail): array
    {
        $items = [];
        $position = 1;

        foreach ($trail as $path => $name) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $name,
                'item' => PublicUrl::to($path),
            ];
        }

        return [
            '@type' => 'BreadcrumbList',
            '@id' => PublicUrl::to((string) array_key_last($trail)).'#breadcrumb',
            'itemListElement' => $items,
        ];
    }

    /**
     * A blog post, written and published by the label.
     *
     * @return array<string, mixed>
     */
    public static function blogPosting(Blog $blog): array
    {
        $url = PublicUrl::to(route('blog.show', $blog->slug, absolute: false));

        return array_filter([
            '@type' => 'BlogPosting',
            '@id' => $url.'#article',
            'headline' => $blog->title,
            'description' => $blog->summary(),
            'image' => $blog->coverUrl(),
            'url' => $url,
            'mainEntityOfPage' => $url,
            'datePublished' => $blog->published_at?->toIso8601String(),
            'dateModified' => $blog->lastModifiedAt()->toIso8601String(),
            'inLanguage' => 'en',
            'author' => ['@id' => self::organizationId()],
            'publisher' => ['@id' => self::organizationId()],
            'isPartOf' => ['@id' => self::websiteId()],
        ], fn (mixed $value): bool => filled($value));
    }

    /**
     * The page's JSON-LD document: the label, the website, then the given nodes.
     *
     * @param  list<array<string, mixed>>  $nodes
     */
    public static function graph(array $nodes = []): string
    {
        return json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [self::organization(), self::website(), ...$nodes],
        ], self::JSON_FLAGS);
    }
}

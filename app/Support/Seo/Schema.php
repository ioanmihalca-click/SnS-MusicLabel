<?php

namespace App\Support\Seo;

use App\Enums\ReleaseFormat;
use App\Models\Artist;
use App\Models\Blog;
use App\Models\Playlist;
use App\Models\Release;
use App\Models\Track;
use App\Support\PlainText;

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
     * A release, with its tracklist and its digital release on the label.
     * Expects the `artists` and `tracks` relations to be loaded.
     *
     * @return array<string, mixed>
     */
    public static function musicAlbum(Release $release): array
    {
        $url = self::releaseUrl($release);

        return self::withoutBlanks([
            '@type' => 'MusicAlbum',
            '@id' => $url.'#album',
            'name' => $release->title,
            'url' => $url,
            'description' => PlainText::fromHtml($release->description) ?: $release->summary(),
            'image' => $release->coverUrl(),
            'datePublished' => $release->released_at?->toDateString(),
            'genre' => $release->genre?->getLabel(),
            'albumReleaseType' => match ($release->format) {
                ReleaseFormat::Single => 'https://schema.org/SingleRelease',
                ReleaseFormat::Ep => 'https://schema.org/EPRelease',
                ReleaseFormat::Album, ReleaseFormat::Compilation => 'https://schema.org/AlbumRelease',
                null => null,
            },
            'albumProductionType' => $release->format === ReleaseFormat::Compilation
                ? 'https://schema.org/CompilationAlbum'
                : null,
            'byArtist' => self::releaseArtists($release),
            'numTracks' => $release->tracks->count() ?: null,
            'track' => $release->tracks
                ->map(fn (Track $track): array => self::musicRecording($track, $url.'#album'))
                ->all(),
            'albumRelease' => [
                self::withoutBlanks([
                    '@type' => 'MusicRelease',
                    '@id' => $url.'#release',
                    'name' => $release->title,
                    'url' => $url,
                    'datePublished' => $release->released_at?->toDateString(),
                    'musicReleaseFormat' => 'https://schema.org/DigitalFormat',
                    'recordLabel' => ['@id' => self::organizationId()],
                ]),
            ],
        ]);
    }

    /**
     * An artist of the roster, linked to their own profiles (never to the label's).
     * Expects the `releases` relation to be loaded.
     *
     * @return array<string, mixed>
     */
    public static function musicGroup(Artist $artist): array
    {
        return self::withoutBlanks([
            ...self::artistReference($artist),
            'description' => PlainText::fromHtml($artist->description) ?: $artist->summary(),
            'image' => $artist->photoUrl(),
            'sameAs' => array_values($artist->profileUrls()),
            'genre' => $artist->releases
                ->map(fn (Release $release): ?string => $release->genre?->getLabel())
                ->filter()
                ->unique()
                ->values()
                ->all(),
            'album' => $artist->releases
                ->map(fn (Release $release): array => [
                    '@type' => 'MusicAlbum',
                    '@id' => self::releaseUrl($release).'#album',
                    'name' => $release->title,
                    'url' => self::releaseUrl($release),
                ])
                ->all(),
        ]);
    }

    /**
     * A Spotify playlist curated by the label.
     *
     * @return array<string, mixed>
     */
    public static function musicPlaylist(Playlist $playlist): array
    {
        return self::withoutBlanks([
            '@type' => 'MusicPlaylist',
            '@id' => PublicUrl::to(route('playlists.index', absolute: false)).'#playlist-'.$playlist->id,
            'name' => $playlist->displayTitle(),
            'description' => $playlist->description,
            'url' => $playlist->spotify_url,
            'image' => $playlist->coverUrl(),
            'author' => ['@id' => self::organizationId()],
        ]);
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

    private static function releaseUrl(Release $release): string
    {
        return PublicUrl::to(route('releases.show', $release->slug, absolute: false));
    }

    /**
     * The artist as referenced from other nodes; the artist page adds the details.
     *
     * @return array{'@type': string, '@id': string, name: string, url: string}
     */
    private static function artistReference(Artist $artist): array
    {
        $url = PublicUrl::to(route('artists.show', $artist->slug, absolute: false));

        return [
            '@type' => 'MusicGroup',
            '@id' => $url.'#artist',
            'name' => $artist->name,
            'url' => $url,
        ];
    }

    /**
     * The roster artists linked to the release, or its typed credit when none is linked.
     *
     * @return list<array<string, string>>
     */
    private static function releaseArtists(Release $release): array
    {
        if ($release->artists->isNotEmpty()) {
            return $release->artists->map(fn (Artist $artist): array => self::artistReference($artist))->values()->all();
        }

        return filled($release->credit) ? [['@type' => 'MusicGroup', 'name' => $release->credit]] : [];
    }

    /**
     * @return array<string, mixed>
     */
    private static function musicRecording(Track $track, string $albumId): array
    {
        return self::withoutBlanks([
            '@type' => 'MusicRecording',
            'name' => $track->fullTitle(),
            'position' => $track->position,
            'duration' => $track->isoDuration(),
            'isrcCode' => $track->isrc,
            'inAlbum' => ['@id' => $albumId],
        ]);
    }

    /**
     * Drop the properties without a value (null, empty strings and empty lists).
     *
     * @param  array<string, mixed>  $node
     * @return array<string, mixed>
     */
    private static function withoutBlanks(array $node): array
    {
        return array_filter($node, fn (mixed $value): bool => filled($value));
    }
}

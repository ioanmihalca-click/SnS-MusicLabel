<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * URL slugs for public catalogue pages (releases, artists).
 */
final class Slug
{
    public const FALLBACK = 'untitled';

    private const MAX_LENGTH = 200;

    /**
     * The base slug for a value: "&" reads as "and" (G&S becomes "g-and-s") and
     * values without letters or digits fall back to "untitled".
     */
    public static function from(string $value): string
    {
        $slug = Str::slug($value, '-', 'en', ['@' => 'at', '&' => 'and']);
        $slug = trim(Str::substr($slug, 0, self::MAX_LENGTH), '-');

        return $slug !== '' ? $slug : self::FALLBACK;
    }

    /**
     * The first free variant of the base slug: "fuego", then "fuego-2", "fuego-3"...
     *
     * @param  callable(string): bool  $isTaken
     */
    public static function unique(string $value, callable $isTaken): string
    {
        $base = self::from($value);
        $slug = $base;
        $suffix = 2;

        while ($isTaken($slug)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}

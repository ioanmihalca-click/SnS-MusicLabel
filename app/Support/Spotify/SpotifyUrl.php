<?php

namespace App\Support\Spotify;

use InvalidArgumentException;

/**
 * An immutable reference to a Spotify entity (track, album, playlist...),
 * parsed from any shape an admin might paste: a share URL (with `?si=` or an
 * `/intl-xx/` prefix), a `spotify:` URI or a legacy embed `<iframe>`.
 */
final class SpotifyUrl
{
    /**
     * @var list<string>
     */
    public const TYPES = ['track', 'album', 'playlist', 'artist', 'episode', 'show'];

    private const ID_PATTERN = '[A-Za-z0-9]{22}';

    private function __construct(
        public readonly string $type,
        public readonly string $id,
    ) {}

    /**
     * Build a reference from a known type and ID, e.g. from curated catalogue data.
     *
     * @throws InvalidArgumentException
     */
    public static function fromParts(string $type, string $id): self
    {
        if (! in_array($type, self::TYPES, true) || ! preg_match('/^'.self::ID_PATTERN.'$/', $id)) {
            throw new InvalidArgumentException("Invalid Spotify {$type} ID [{$id}].");
        }

        return new self($type, $id);
    }

    /**
     * Parse a URL, URI or embed code. Returns null for anything that is not an
     * open.spotify.com link, including `spotify.link` short links, which can
     * only be resolved over the network.
     */
    public static function parse(?string $value): ?self
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (str_contains($value, '<')) {
            if (! preg_match('/<iframe\b[^>]*?\bsrc\s*=\s*(["\'])(.*?)\1/is', $value, $matches)) {
                return null;
            }

            $value = trim(html_entity_decode($matches[2]));
        }

        $types = implode('|', self::TYPES);

        if (preg_match('/^spotify:('.$types.'):('.self::ID_PATTERN.')$/', $value, $matches)) {
            return new self($matches[1], $matches[2]);
        }

        $urlPattern = '#^(?:https?://)?open\.spotify\.com/(?:intl-[a-z]{2}(?:-[a-z]{2,4})?/)?(?:embed/)?('.$types.')/('.self::ID_PATTERN.')(?:[/?\#]|$)#i';

        if (preg_match($urlPattern, $value, $matches)) {
            return new self(strtolower($matches[1]), $matches[2]);
        }

        return null;
    }

    /**
     * Whether the value is a short share link (spotify.link / spotify.app.link)
     * that has to be opened in a browser to reveal the real open.spotify.com URL.
     */
    public static function isShortLink(?string $value): bool
    {
        return (bool) preg_match('#^(?:https?://)?(?:[a-z0-9-]+\.)*spotify(?:\.app)?\.link(?:/|$)#i', trim((string) $value));
    }

    public function is(string ...$types): bool
    {
        return in_array($this->type, $types, true);
    }

    /**
     * Whether the footer player can load it: Spotify's embed plays an album,
     * a track or a playlist. Artist pages, episodes and shows stay links.
     */
    public function isPlayable(): bool
    {
        return $this->is('album', 'track', 'playlist');
    }

    public function url(): string
    {
        return "https://open.spotify.com/{$this->type}/{$this->id}";
    }

    public function uri(): string
    {
        return "spotify:{$this->type}:{$this->id}";
    }

    public function embedSrc(): string
    {
        return "https://open.spotify.com/embed/{$this->type}/{$this->id}";
    }
}

<?php

namespace App\Support\Spotify;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;

/**
 * Accepts anything SpotifyUrl can parse, optionally restricted to some entity types.
 */
final class SpotifyUrlRule implements ValidationRule
{
    /**
     * @var list<string>
     */
    private array $types;

    public function __construct(string ...$types)
    {
        $this->types = array_values($types);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        if (is_string($value) && SpotifyUrl::isShortLink($value)) {
            $fail('Short spotify.link links are not supported. Open the link in a browser and paste the full open.spotify.com address.');

            return;
        }

        $spotifyUrl = is_string($value) ? SpotifyUrl::parse($value) : null;

        if ($spotifyUrl === null || ($this->types !== [] && ! $spotifyUrl->is(...$this->types))) {
            $fail($this->message());
        }
    }

    private function message(): string
    {
        if ($this->types === []) {
            return 'The :attribute must be a Spotify link (https://open.spotify.com/...).';
        }

        $example = "https://open.spotify.com/{$this->types[0]}/...";

        return 'The :attribute must be a Spotify '.Arr::join($this->types, ', ', ' or ')." link, e.g. {$example}";
    }
}

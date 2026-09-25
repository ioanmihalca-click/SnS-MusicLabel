<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Track durations: stored as seconds, typed and displayed as "m:ss".
 */
final class Duration
{
    public const PATTERN = '/^\d{1,3}:[0-5]\d$/';

    /**
     * @throws InvalidArgumentException
     */
    public static function toSeconds(string $duration): int
    {
        $duration = trim($duration);

        if (! self::isValid($duration)) {
            throw new InvalidArgumentException("Invalid duration [{$duration}], expected m:ss.");
        }

        [$minutes, $seconds] = explode(':', $duration);

        return ((int) $minutes * 60) + (int) $seconds;
    }

    public static function format(?int $seconds): ?string
    {
        if ($seconds === null || $seconds < 0) {
            return null;
        }

        return sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
    }

    public static function isValid(string $duration): bool
    {
        return (bool) preg_match(self::PATTERN, $duration);
    }
}

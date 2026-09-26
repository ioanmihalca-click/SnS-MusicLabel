<?php

namespace App\Support\Seo;

use Illuminate\Container\Attributes\Scoped;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Tells Bing, Yandex, Seznam, Naver and the other IndexNow engines which
 * public URLs changed. URLs collected during a request are sent once, in a
 * single call, after the response has reached the visitor; failures are only
 * logged. Enabled in production only (`services.indexnow.enabled`).
 */
#[Scoped]
final class IndexNow
{
    public const ENDPOINT = 'https://api.indexnow.org/indexnow';

    private const TIMEOUT_SECONDS = 3;

    /**
     * @var array<string, true>
     */
    private array $pendingUrls = [];

    /**
     * The verification key, derived from the app key so it needs no configuration.
     * It is served as plain text at `/{key}.txt`.
     */
    public static function key(): string
    {
        return substr(hash_hmac('sha256', 'indexnow', (string) config('app.key')), 0, 32);
    }

    public static function keyLocation(): string
    {
        return PublicUrl::to(self::key().'.txt');
    }

    public function isEnabled(): bool
    {
        return (bool) config('services.indexnow.enabled');
    }

    /**
     * Queue absolute URLs for the single submission sent at the end of the request.
     */
    public function submit(string ...$urls): void
    {
        if (! $this->isEnabled() || $urls === []) {
            return;
        }

        foreach ($urls as $url) {
            $this->pendingUrls[$url] = true;
        }

        defer(fn () => $this->flush(), 'indexnow');
    }

    /**
     * Send the queued URLs now.
     */
    public function flush(): void
    {
        $urls = array_keys($this->pendingUrls);
        $this->pendingUrls = [];

        if ($urls === []) {
            return;
        }

        $payload = [
            'host' => parse_url((string) config('app.url'), PHP_URL_HOST),
            'key' => self::key(),
            'keyLocation' => self::keyLocation(),
            'urlList' => $urls,
        ];

        try {
            $response = Http::connectTimeout(self::TIMEOUT_SECONDS)
                ->timeout(self::TIMEOUT_SECONDS)
                ->withBody(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), 'application/json; charset=utf-8')
                ->post(self::ENDPOINT);
        } catch (ConnectionException $exception) {
            Log::warning('IndexNow submission failed.', ['urls' => $urls, 'error' => $exception->getMessage()]);

            return;
        }

        if (! in_array($response->status(), [200, 202], true)) {
            Log::warning('IndexNow rejected the submission.', ['urls' => $urls, 'status' => $response->status()]);
        }
    }
}

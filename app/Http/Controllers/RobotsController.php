<?php

namespace App\Http\Controllers;

use App\Support\Seo\PublicUrl;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Search engines and the agents that read a page for a user.
     *
     * @var list<string>
     */
    public const ALLOWED_AGENTS = [
        'Googlebot',
        'Bingbot',
        'OAI-SearchBot',
        'ChatGPT-User',
        'Claude-SearchBot',
        'Claude-User',
        'PerplexityBot',
        'Perplexity-User',
        'Applebot',
    ];

    /**
     * Crawlers that collect content to train AI models.
     *
     * @var list<string>
     */
    public const TRAINING_AGENTS = [
        'GPTBot',
        'ClaudeBot',
        'Google-Extended',
        'Applebot-Extended',
        'CCBot',
        'meta-externalagent',
        'Bytespider',
    ];

    /**
     * A crawler obeys only the most specific group naming it, so each group
     * repeats every rule that applies to it. Outside production the whole
     * site is closed, so staging copies never get indexed.
     */
    public function __invoke(): Response
    {
        $groups = app()->isProduction()
            ? [
                $this->group(self::ALLOWED_AGENTS, ['Disallow: /admin']),
                $this->group(self::TRAINING_AGENTS, ['Disallow: /']),
                $this->group(['*'], ['Disallow: /admin', 'Content-Signal: '.$this->contentSignal()]),
                'Sitemap: '.PublicUrl::to(route('sitemap', absolute: false)),
            ]
            : [$this->group(['*'], ['Disallow: /'])];

        return response(implode("\n\n", $groups)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /**
     * @param  list<string>  $agents
     * @param  list<string>  $rules
     */
    private function group(array $agents, array $rules): string
    {
        return collect($agents)
            ->map(fn (string $agent): string => "User-agent: {$agent}")
            ->concat($rules)
            ->implode("\n");
    }

    /**
     * The same signals as the Markdown responses' `Content-Signal` header,
     * e.g. "search=yes, ai-input=yes, ai-train=no".
     */
    private function contentSignal(): string
    {
        return collect(config('markdown-response.content_signals', []))
            ->map(fn (string $value, string $signal): string => "{$signal}={$value}")
            ->implode(', ');
    }
}

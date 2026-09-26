<?php

namespace App\Http\Controllers;

use App\Support\Seo\PageCache;
use App\Support\Seo\PublicPage;
use App\Support\Seo\PublicPages;
use App\Support\Seo\PublicUrl;
use App\Support\Seo\SeoData;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LlmsTxtController extends Controller
{
    public const SUMMARY = "Snow 'n' Stuff is an independent electronic music label, artist management and music production company based in Stockholm and Romania, founded in 2020. It releases Tech House, Deep House, House and Techno, and curates Spotify playlists.";

    /**
     * llms.txt (https://llmstxt.org): a short introduction for language models,
     * then a list of the public pages, linked to their Markdown versions.
     */
    public function __invoke(PublicPages $publicPages): Response
    {
        $text = PageCache::remember('llms.txt', fn (): string => $this->render($publicPages->all()));

        return response($text, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /**
     * @param  Collection<int, PublicPage>  $pages
     */
    private function render(Collection $pages): string
    {
        $blocks = [
            '# '.SeoData::SITE_NAME,
            '> '.self::SUMMARY,
            'Every page is also available as Markdown: add `.md` to its URL (the homepage is '.PublicUrl::markdown('/').') or request it with the `Accept: text/markdown` header.',
        ];

        foreach ($pages->groupBy('section') as $section => $sectionPages) {
            $blocks[] = "## {$section}\n\n".$sectionPages
                ->map(fn (PublicPage $page): string => $this->link($page))
                ->implode("\n");
        }

        return implode("\n\n", $blocks)."\n";
    }

    private function link(PublicPage $page): string
    {
        $title = str_replace(['[', ']'], ['\[', '\]'], Str::squish($page->title));

        return "- [{$title}]({$page->markdownUrl()}): ".Str::squish($page->description);
    }
}

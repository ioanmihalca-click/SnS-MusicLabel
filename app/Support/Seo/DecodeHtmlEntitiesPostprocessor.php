<?php

namespace App\Support\Seo;

use Spatie\MarkdownResponse\Postprocessors\Postprocessor;

/**
 * league/html-to-markdown leaves the text HTML-escaped ("G&amp;S", "A&amp;R"),
 * which Markdown readers show literally. Runs after RemoveHtmlTagsPostprocessor,
 * so a decoded "<" in the text is never mistaken for a tag.
 */
final class DecodeHtmlEntitiesPostprocessor implements Postprocessor
{
    public function __invoke(string $markdown): string
    {
        return html_entity_decode($markdown, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Plain text from the rich HTML written in the admin, for meta descriptions,
 * JSON-LD and llms.txt.
 */
final class PlainText
{
    private const BLOCK_TAG_PATTERN = '#<(/?)(p|div|br|li|ul|ol|h[1-6]|blockquote)\b#i';

    /**
     * The readable text: tags removed (paragraphs stay apart), entities decoded
     * ("G&amp;S" becomes "G&S") and whitespace collapsed.
     */
    public static function fromHtml(?string $html): string
    {
        $text = strip_tags((string) preg_replace(self::BLOCK_TAG_PATTERN, ' <$1$2', (string) $html));

        return Str::squish(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * The start of the text, e.g. for a meta description.
     */
    public static function excerpt(?string $html, int $limit = 160): string
    {
        return Str::limit(self::fromHtml($html), $limit);
    }
}

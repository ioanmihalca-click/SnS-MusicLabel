<?php

namespace App\Support;

use App\Support\Spotify\SpotifyUrl;
use Dom\Element;
use Dom\HTMLDocument;
use Dom\Node;
use Dom\Text;
use SplObjectStorage;

/**
 * The HTML of a blog post, cleaned each time it is shown; the stored content
 * is never rewritten.
 *
 * The posts come from two editors (TinyMCE on the old site, Filament's Trix
 * now) and carry pasted inline styles, classes and empty paragraphs. Only a
 * list of tags is kept, without their styling attributes; `<span>` and
 * `<font>` are unwrapped, empty paragraphs dropped and `<h1>` becomes `<h2>`
 * (the post title is the page's only `<h1>`).
 *
 * Embedded players are kept only from Spotify, Beatport and nfan.link, as a
 * responsive `<figure class="article-embed">` with a visible link under the
 * player, so the Markdown version still leads to the music. Trix cannot store
 * iframes, so a Spotify link alone in a paragraph also becomes a player.
 */
final class ArticleHtml
{
    /**
     * The tags kept, with the attributes each may keep.
     *
     * @var array<string, list<string>>
     */
    private const ALLOWED_TAGS = [
        'p' => [], 'br' => [], 'hr' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [], 'del' => [], 'small' => [], 'sub' => [], 'sup' => [],
        'h2' => [], 'h3' => [], 'h4' => [], 'h5' => [], 'h6' => [],
        'ul' => [], 'ol' => [], 'li' => [],
        'blockquote' => [], 'pre' => [], 'code' => [],
        'a' => ['href', 'title', 'target'],
        'img' => ['src', 'alt', 'width', 'height'],
        'figure' => [], 'figcaption' => [],
        'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [], 'th' => [], 'td' => [],
    ];

    /**
     * Elements removed together with their content.
     */
    private const REMOVED_SELECTOR = 'script, style, noscript, template, object, embed, applet, form, input, button, select, textarea, svg, math, link, meta, base, title, frame, frameset, audio, video, canvas';

    /**
     * Wrappers a lone Spotify link may sit in, e.g. `<p><strong><a>...</a></strong></p>`.
     *
     * @var list<string>
     */
    private const INLINE_WRAPPERS = ['strong', 'b', 'em', 'i', 'u'];

    /**
     * Elements a `<div>` can hold that make it a container rather than a paragraph.
     *
     * @var list<string>
     */
    private const BLOCK_TAGS = ['p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'blockquote', 'pre', 'figure', 'table', 'hr', 'iframe'];

    /**
     * The URL schemes a link may use; relative links and fragments are kept too.
     *
     * @var list<string>
     */
    private const SAFE_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    private const IFRAME_ALLOW = 'autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture';

    public static function render(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        $document = HTMLDocument::createFromString(
            '<!DOCTYPE html><html><head></head><body>'.$html.'</body></html>',
            LIBXML_NOERROR,
            'UTF-8',
        );
        $body = $document->body;

        if ($body === null) {
            return '';
        }

        foreach ($body->querySelectorAll(self::REMOVED_SELECTOR) as $element) {
            $element->remove();
        }

        foreach (iterator_to_array($body->getElementsByTagName('*'), false) as $element) {
            self::sanitize($document, $element);
        }

        $spotifyUrlsInIframes = self::spotifyUrlsInIframes($body);
        self::replaceIframes($document, $body);
        self::embedLoneSpotifyLinks($document, $body, $spotifyUrlsInIframes);
        self::removeEmptyElements($body);

        return trim($body->innerHTML);
    }

    /**
     * Keep an allowed element with its allowed attributes only; rename or
     * unwrap the rest. Iframes are left for replaceIframes().
     */
    private static function sanitize(HTMLDocument $document, Element $element): void
    {
        $tag = strtolower($element->localName);

        if ($tag === 'iframe') {
            return;
        }

        if ($tag === 'h1') {
            $element = self::rename($document, $element, 'h2');
            $tag = 'h2';
        }

        if ($tag === 'div') {
            if (self::containsBlocks($element)) {
                self::unwrap($element);

                return;
            }

            $element = self::rename($document, $element, 'p');
            $tag = 'p';
        }

        if (! array_key_exists($tag, self::ALLOWED_TAGS)) {
            self::unwrap($element);

            return;
        }

        foreach ($element->getAttributeNames() as $attribute) {
            if (! in_array(strtolower($attribute), self::ALLOWED_TAGS[$tag], true)) {
                $element->removeAttribute($attribute);
            }
        }

        match ($tag) {
            'a' => self::sanitizeLink($element),
            'img' => self::sanitizeImage($element),
            default => null,
        };
    }

    /**
     * Unwrap links to `javascript:` and other unsafe schemes; external links
     * opened in a new tab get `rel="noopener"`.
     */
    private static function sanitizeLink(Element $link): void
    {
        $href = $link->getAttribute('href');

        if ($href === null || ! self::isSafeUrl($href, self::SAFE_SCHEMES)) {
            self::unwrap($link);

            return;
        }

        if ($link->getAttribute('target') === '_blank') {
            $link->setAttribute('rel', 'noopener');
        } else {
            $link->removeAttribute('target');
        }
    }

    private static function sanitizeImage(Element $image): void
    {
        $src = $image->getAttribute('src');

        if ($src === null || ! self::isSafeUrl($src, ['http', 'https'])) {
            $image->remove();

            return;
        }

        $image->setAttribute('loading', 'lazy');
        $image->setAttribute('decoding', 'async');
    }

    /**
     * Whether the URL is relative or uses one of the given schemes. Control
     * characters and spaces are ignored, as browsers do ("java\tscript:").
     *
     * @param  list<string>  $schemes
     */
    private static function isSafeUrl(string $url, array $schemes): bool
    {
        $normalized = (string) preg_replace('/[\x00-\x20]+/', '', $url);

        if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $normalized, $matches)) {
            return in_array(strtolower($matches[1]), $schemes, true);
        }

        return true;
    }

    /**
     * @return list<string> The open.spotify.com URLs already embedded with an iframe.
     */
    private static function spotifyUrlsInIframes(Element $body): array
    {
        $urls = [];

        foreach ($body->querySelectorAll('iframe') as $iframe) {
            $spotifyUrl = SpotifyUrl::parse($iframe->getAttribute('src'));

            if ($spotifyUrl !== null) {
                $urls[] = $spotifyUrl->url();
            }
        }

        return $urls;
    }

    /**
     * A paragraph holding only a Spotify link, or only a Spotify URL, becomes a
     * player; the link text, when it is not the URL itself, stays as the
     * caption. A link to music already embedded in the post stays a link.
     *
     * @param  list<string>  $embeddedUrls
     */
    private static function embedLoneSpotifyLinks(HTMLDocument $document, Element $body, array $embeddedUrls): void
    {
        foreach (iterator_to_array($body->querySelectorAll('p'), false) as $paragraph) {
            $content = self::loneContent($paragraph);

            if ($content === null) {
                continue;
            }

            $isLink = $content instanceof Element;
            $spotifyUrl = SpotifyUrl::parse($isLink ? $content->getAttribute('href') : trim($content->textContent ?? ''));

            if ($spotifyUrl === null || in_array($spotifyUrl->url(), $embeddedUrls, true)) {
                continue;
            }

            $linkText = $isLink ? self::squish($content->textContent ?? '') : '';
            $caption = $linkText === '' || SpotifyUrl::parse($linkText) !== null || str_starts_with($linkText, 'http')
                ? 'Listen on Spotify'
                : $linkText;

            $paragraph->replaceWith(self::embed($document, $spotifyUrl->embedSrc(), 'Spotify player', 352, $spotifyUrl->url(), $caption));
            $embeddedUrls[] = $spotifyUrl->url();
        }
    }

    /**
     * The paragraph's only content, looking through inline wrappers: a link,
     * a text node without spaces, or null when there is anything else.
     */
    private static function loneContent(Element $paragraph): Element|Text|null
    {
        $node = $paragraph;

        while (true) {
            $children = array_values(array_filter(
                iterator_to_array($node->childNodes, false),
                fn (Node $child): bool => ! self::isBlankNode($child),
            ));

            if (count($children) !== 1) {
                return null;
            }

            $child = $children[0];

            if ($child instanceof Text) {
                $text = trim($child->data);

                return preg_match('/\s/u', $text) ? null : $child;
            }

            if (! $child instanceof Element) {
                return null;
            }

            $tag = strtolower($child->localName);

            if ($tag === 'a') {
                return $child;
            }

            if (! in_array($tag, self::INLINE_WRAPPERS, true)) {
                return null;
            }

            $node = $child;
        }
    }

    /**
     * Replace each allowed iframe with a responsive figure and drop the others.
     * A figure cannot sit inside a paragraph, so an iframe nested in the post's
     * text moves right after the top-level block that held it.
     */
    private static function replaceIframes(HTMLDocument $document, Element $body): void
    {
        /** @var SplObjectStorage<Node, Node> $lastInsertedAfter */
        $lastInsertedAfter = new SplObjectStorage;

        foreach (iterator_to_array($body->querySelectorAll('iframe'), false) as $iframe) {
            $figure = self::figureForIframe($document, $iframe);

            if ($figure === null) {
                $iframe->remove();

                continue;
            }

            $block = $iframe;

            while ($block->parentNode !== null && ! $block->parentNode->isSameNode($body)) {
                $block = $block->parentNode;
            }

            if ($block->isSameNode($iframe)) {
                $iframe->replaceWith($figure);

                continue;
            }

            ($lastInsertedAfter[$block] ?? $block)->after($figure);
            $lastInsertedAfter[$block] = $figure;
            $iframe->remove();
        }
    }

    private static function figureForIframe(HTMLDocument $document, Element $iframe): ?Element
    {
        $src = trim((string) $iframe->getAttribute('src'));
        $height = ctype_digit((string) $iframe->getAttribute('height')) ? (int) $iframe->getAttribute('height') : null;

        if (! self::isSafeUrl($src, ['http', 'https'])) {
            return null;
        }

        $host = strtolower((string) parse_url($src, PHP_URL_HOST));
        $https = (string) preg_replace('#^(?:https?:)?//#i', 'https://', $src);

        return match ($host) {
            'open.spotify.com' => ($spotifyUrl = SpotifyUrl::parse($src)) === null
                ? null
                : self::embed($document, $spotifyUrl->embedSrc(), 'Spotify player', $height ?? 352, $spotifyUrl->url(), 'Listen on Spotify'),
            'embed.beatport.com' => self::embed($document, $https, 'Beatport player', $height ?? 162, $https, 'Listen on Beatport'),
            'nfan.link' => self::embed($document, $https, 'Listen on all platforms', $height ?? 400, $https, 'Listen on all platforms'),
            default => null,
        };
    }

    /**
     * `<figure class="article-embed"><iframe ...><figcaption><a ...>` with the given player.
     */
    private static function embed(HTMLDocument $document, string $src, string $title, int $height, string $linkUrl, string $linkText): Element
    {
        $iframe = $document->createElement('iframe');
        $iframe->setAttribute('src', $src);
        $iframe->setAttribute('title', $title);
        $iframe->setAttribute('height', (string) $height);
        $iframe->setAttribute('loading', 'lazy');
        $iframe->setAttribute('allow', self::IFRAME_ALLOW);

        $link = $document->createElement('a');
        $link->setAttribute('href', $linkUrl);
        $link->setAttribute('target', '_blank');
        $link->setAttribute('rel', 'noopener');
        $link->textContent = $linkText;

        $caption = $document->createElement('figcaption');
        $caption->append($link);

        $figure = $document->createElement('figure');
        $figure->setAttribute('class', 'article-embed');
        $figure->append($iframe, $caption);

        return $figure;
    }

    /**
     * Drop paragraphs and headings with no text (`<p>&nbsp;</p>`), captions
     * left empty, and line breaks at the start or end of a paragraph.
     */
    private static function removeEmptyElements(Element $body): void
    {
        foreach (iterator_to_array($body->querySelectorAll('p, h2, h3, h4, h5, h6, figcaption'), false) as $element) {
            if (self::squish($element->textContent ?? '') === '' && $element->querySelector('img, iframe') === null) {
                $element->remove();
            }
        }

        foreach ($body->querySelectorAll('p') as $paragraph) {
            self::trimLineBreaks($paragraph, fn (Element $paragraph): ?Node => $paragraph->firstChild, fn (Node $node): ?Node => $node->nextSibling);
            self::trimLineBreaks($paragraph, fn (Element $paragraph): ?Node => $paragraph->lastChild, fn (Node $node): ?Node => $node->previousSibling);
        }
    }

    /**
     * Remove the `<br>` elements (and blank text) at one end of the paragraph.
     *
     * @param  callable(Element): ?Node  $edge
     * @param  callable(Node): ?Node  $inward
     */
    private static function trimLineBreaks(Element $paragraph, callable $edge, callable $inward): void
    {
        $node = $edge($paragraph);

        while ($node !== null && (self::isBlankNode($node) || ($node instanceof Element && strtolower($node->localName) === 'br'))) {
            $next = $inward($node);
            $node->remove();
            $node = $next;
        }
    }

    /**
     * Whitespace-only text (non-breaking spaces included) or a comment.
     */
    private static function isBlankNode(Node $node): bool
    {
        if ($node instanceof Text) {
            return self::squish($node->data) === '';
        }

        return $node->nodeType === XML_COMMENT_NODE;
    }

    private static function squish(string $text): string
    {
        return trim((string) preg_replace('/[\s\x{00A0}]+/u', ' ', $text));
    }

    private static function containsBlocks(Element $element): bool
    {
        foreach ($element->children as $child) {
            if (in_array(strtolower($child->localName), self::BLOCK_TAGS, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Replace the element with a new one of another tag, keeping its children
     * (attributes are dropped by sanitize() anyway).
     */
    private static function rename(HTMLDocument $document, Element $element, string $tag): Element
    {
        $renamed = $document->createElement($tag);

        foreach ($element->getAttributeNames() as $attribute) {
            $renamed->setAttribute($attribute, (string) $element->getAttribute($attribute));
        }

        $renamed->append(...iterator_to_array($element->childNodes, false));
        $element->replaceWith($renamed);

        return $renamed;
    }

    private static function unwrap(Element $element): void
    {
        $element->replaceWith(...iterator_to_array($element->childNodes, false));
    }
}

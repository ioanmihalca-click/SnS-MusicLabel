<?php

namespace App\Support\Seo;

use Dom\Element;
use Dom\HTMLDocument;
use Dom\Node;
use Dom\Text;
use Dom\XPath;
use Spatie\MarkdownResponse\Preprocessors\Preprocessor;

/**
 * Reduces a page to its `<main>` element before it is converted to Markdown,
 * so agents get the content without the navigation, footer or scripts.
 *
 * Inside `<main>` it also drops interface-only markup: anything marked with
 * `data-markdown-ignore` (share buttons, loading overlays...), icons, embeds
 * and controls. Unlike the package's own preprocessors, it keeps `<header>`
 * elements, which hold a blog post's title.
 */
final class MainContentPreprocessor implements Preprocessor
{
    private const REMOVED_SELECTOR = '[data-markdown-ignore], script, style, svg, iframe, noscript, template, button, form';

    /**
     * Elements that start on a new line when rendered, so whitespace around them is not content.
     *
     * @var list<string>
     */
    private const BLOCK_ELEMENTS = [
        'address', 'article', 'aside', 'blockquote', 'dd', 'details', 'div', 'dl', 'dt',
        'figcaption', 'figure', 'footer', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header',
        'hr', 'li', 'main', 'nav', 'ol', 'p', 'pre', 'section', 'summary', 'table', 'ul',
    ];

    public function __invoke(string $html): string
    {
        $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);

        $content = $document->querySelector('main') ?? $document->body;

        if (! $content instanceof Element) {
            return '';
        }

        foreach ($content->querySelectorAll(self::REMOVED_SELECTOR) as $element) {
            $element->remove();
        }

        // Comments (e.g. Livewire's `<!--[if BLOCK]>` markers) and removals leave neighbouring
        // text nodes apart; merged, their whitespace can be trimmed as one.
        foreach (iterator_to_array((new XPath($document))->query('.//comment()', $content), false) as $comment) {
            $comment->remove();
        }

        $content->normalize();

        $this->turnFiguresIntoDivs($document, $content);
        $this->unwrapEmailLinks($content);
        $this->trimWhitespaceAroundBlocks($document, $content);
        $this->trimWhitespaceInsideLinks($document, $content);
        $this->separateTermsFromDescriptions($content);

        return $content->innerHTML;
    }

    /**
     * The converter only starts a new line around the blocks it knows, and
     * `<figure>` is not one of them: a player's caption in a blog post would
     * run into the next paragraph.
     */
    private function turnFiguresIntoDivs(HTMLDocument $document, Element $content): void
    {
        foreach (iterator_to_array($content->querySelectorAll('figure, figcaption'), false) as $figure) {
            $div = $document->createElement('div');
            $div->append(...iterator_to_array($figure->childNodes, false));
            $figure->replaceWith($div);
        }
    }

    /**
     * The converter has no definition lists: "Released" and "04.04.2025" would
     * run together, so each term ends with a colon.
     */
    private function separateTermsFromDescriptions(Element $content): void
    {
        foreach ($content->querySelectorAll('dt') as $term) {
            $term->append(str_ends_with(rtrim($term->textContent), ':') ? ' ' : ': ');
        }
    }

    /**
     * "[Listen now](...)" rather than "[ Listen now ](...)": the template
     * whitespace around a link's text (often next to a removed icon) goes.
     */
    private function trimWhitespaceInsideLinks(HTMLDocument $document, Element $content): void
    {
        $xpath = new XPath($document);

        foreach ($content->querySelectorAll('a') as $link) {
            /** @var list<Text> $textNodes */
            $textNodes = iterator_to_array($xpath->query('.//text()', $link), false);

            $this->trimEdge($textNodes, fn (Text $text): string => ltrim($text->data));
            $this->trimEdge(array_reverse($textNodes), fn (Text $text): string => rtrim($text->data));
        }
    }

    /**
     * Trim the first text nodes of the list until one keeps some text.
     *
     * @param  list<Text>  $textNodes
     * @param  callable(Text): string  $trim
     */
    private function trimEdge(array $textNodes, callable $trim): void
    {
        foreach ($textNodes as $text) {
            $text->data = $trim($text);

            if ($text->data !== '') {
                return;
            }
        }
    }

    /**
     * An e-mail link showing its own address becomes the plain address: the
     * converter would write it as an autolink (<name@example.com>), which the
     * package's RemoveHtmlTagsPostprocessor then deletes.
     */
    private function unwrapEmailLinks(Element $content): void
    {
        foreach ($content->querySelectorAll('a[href^="mailto:"]') as $link) {
            $address = trim($link->textContent);

            if ($link->getAttribute('href') === 'mailto:'.$address) {
                $link->replaceWith($address);
            }
        }
    }

    /**
     * Drop the template indentation next to block boundaries, as a browser does
     * when rendering; left in place it would indent the Markdown lines (four
     * spaces turn a heading into a code block). Whitespace between inline
     * elements is kept, so words never run together.
     */
    private function trimWhitespaceAroundBlocks(HTMLDocument $document, Element $content): void
    {
        /** @var iterable<Text> $textNodes */
        $textNodes = (new XPath($document))->query('.//text()[not(ancestor::pre)]', $content);

        foreach ($textNodes as $text) {
            $parentIsBlock = $this->isBlock($text->parentNode);
            $trimStart = $this->isBlock($text->previousSibling) || ($text->previousSibling === null && $parentIsBlock);
            $trimEnd = $this->isBlock($text->nextSibling) || ($text->nextSibling === null && $parentIsBlock);

            if (trim($text->data) === '') {
                if ($trimStart || $trimEnd) {
                    $text->remove();
                }

                continue;
            }

            if ($trimStart) {
                $text->data = ltrim($text->data);
            }

            if ($trimEnd) {
                $text->data = rtrim($text->data);
            }
        }
    }

    private function isBlock(?Node $node): bool
    {
        return $node instanceof Element && in_array(strtolower($node->localName), self::BLOCK_ELEMENTS, true);
    }
}

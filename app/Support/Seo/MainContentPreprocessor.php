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

        $this->unwrapEmailLinks($content);
        $this->trimWhitespaceAroundBlocks($document, $content);

        return $content->innerHTML;
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

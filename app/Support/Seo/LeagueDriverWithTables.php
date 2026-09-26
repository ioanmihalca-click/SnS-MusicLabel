<?php

namespace App\Support\Seo;

use League\HTMLToMarkdown\Converter\TableConverter;
use League\HTMLToMarkdown\HtmlConverter;
use Spatie\MarkdownResponse\Drivers\LeagueDriver;

/**
 * The package's league/html-to-markdown driver, with tables written as
 * Markdown tables (e.g. the cookie list on /privacy) instead of their cells
 * run together. Bound in AppServiceProvider.
 */
final class LeagueDriverWithTables extends LeagueDriver
{
    public function convert(string $html): string
    {
        $converter = new HtmlConverter($this->options);
        $converter->getEnvironment()->addConverter(new TableConverter);

        return $converter->convert($html);
    }
}

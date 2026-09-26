<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

/**
 * How to use the admin, for the label's team. The text lives in
 * resources/views/filament/pages/manual.blade.php: update it when a form changes.
 */
class Manual extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Help';

    protected static ?string $navigationLabel = 'Manual';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Admin manual';

    protected static string $view = 'filament.pages.manual';
}

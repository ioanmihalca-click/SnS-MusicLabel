<?php

namespace App\Filament\Resources\FeaturedTrackResource\Pages;

use App\Filament\Resources\FeaturedTrackResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeaturedTracks extends ListRecords
{
    protected static string $resource = FeaturedTrackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

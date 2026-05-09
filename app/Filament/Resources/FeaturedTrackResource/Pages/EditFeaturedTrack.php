<?php

namespace App\Filament\Resources\FeaturedTrackResource\Pages;

use App\Filament\Resources\FeaturedTrackResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFeaturedTrack extends EditRecord
{
    protected static string $resource = FeaturedTrackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

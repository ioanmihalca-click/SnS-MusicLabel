<?php

namespace App\Filament\Resources\DemoSubmissionResource\Pages;

use App\Filament\Resources\DemoSubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDemoSubmission extends EditRecord
{
    protected static string $resource = DemoSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

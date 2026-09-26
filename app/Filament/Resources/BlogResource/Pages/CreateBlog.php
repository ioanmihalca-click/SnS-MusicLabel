<?php

namespace App\Filament\Resources\BlogResource\Pages;

use App\Filament\Resources\BlogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBlog extends CreateRecord
{
    protected static string $resource = BlogResource::class;

    /**
     * Set while "Publish now" creates the post, so it goes live whatever the Publish Date says.
     */
    protected bool $isPublishingNow = false;

    /**
     * @return array<Actions\Action | Actions\ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getPublishNowFormAction(),
            ...(static::canCreateAnother() ? [$this->getCreateAnotherFormAction()] : []),
            $this->getCancelFormAction(),
        ];
    }

    protected function getPublishNowFormAction(): Actions\Action
    {
        return Actions\Action::make('publishNow')
            ->label('Publish now')
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->action(fn () => $this->publishNow());
    }

    /**
     * Create the post exactly like "Create" does (same validation), dated now.
     */
    protected function publishNow(): void
    {
        $this->isPublishingNow = true;

        try {
            $this->create();
        } finally {
            $this->isPublishingNow = false;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($this->isPublishingNow) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return $this->isPublishingNow ? 'Published' : parent::getCreatedNotificationTitle();
    }
}

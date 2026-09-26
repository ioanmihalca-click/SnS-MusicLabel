<?php

namespace App\Filament\Resources\BlogResource\Pages;

use App\Filament\Resources\BlogResource;
use App\Models\Blog;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBlog extends EditRecord
{
    protected static string $resource = BlogResource::class;

    /**
     * Set while "Publish now" saves the form, so the post goes live whatever the Publish Date says.
     */
    protected bool $isPublishingNow = false;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('publishNow')
                ->label('Publish now')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn (Blog $record): bool => ! $record->isPublished())
                ->requiresConfirmation(fn (Blog $record): bool => BlogResource::hasEmbeds($record))
                ->modalDescription(fn (Blog $record): ?string => BlogResource::hasEmbeds($record) ? BlogResource::EMBEDS_WARNING : null)
                ->action(fn () => $this->publishNow()),
            Actions\Action::make('unpublish')
                ->label('Unpublish')
                ->icon('heroicon-o-eye-slash')
                ->color('warning')
                ->visible(fn (Blog $record): bool => $record->isPublished())
                ->requiresConfirmation()
                ->modalDescription(BlogResource::UNPUBLISH_DESCRIPTION)
                ->action(fn () => $this->unpublish()),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Save the form (same validation as "Save") with the post dated now.
     */
    protected function publishNow(): void
    {
        $this->isPublishingNow = true;

        try {
            $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
        } finally {
            $this->isPublishingNow = false;
        }

        $this->refreshFormData(['published_at']);

        Notification::make()
            ->success()
            ->title('Published')
            ->send();
    }

    /**
     * Take the post off the site; unsaved edits in the form stay in the form, unsaved.
     */
    protected function unpublish(): void
    {
        $this->getRecord()->update(['published_at' => null]);

        $this->refreshFormData(['published_at']);

        Notification::make()
            ->success()
            ->title('Unpublished')
            ->send();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->isPublishingNow) {
            $data['published_at'] = now();
        }

        return $data;
    }
}

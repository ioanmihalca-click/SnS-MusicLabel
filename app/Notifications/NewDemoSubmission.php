<?php

namespace App\Notifications;

use App\Filament\Resources\DemoSubmissionResource;
use App\Models\DemoSubmission;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells the label that a demo came in through /demos. Sent on demand to
 * `services.demos.notify_email`; replying answers the artist.
 */
class NewDemoSubmission extends Notification
{
    public function __construct(public readonly DemoSubmission $submission) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $submission = $this->submission;

        $message = (new MailMessage)
            ->subject('New demo: '.$submission->artist_name)
            ->replyTo($submission->email, $submission->artist_name)
            ->greeting('New demo')
            ->line('Artist or project: '.$this->escape($submission->artist_name))
            ->line('Email: '.$submission->email)
            ->line('Private link: '.$submission->link)
            ->line('Genre: '.($submission->genreLabel() ?? 'Not given'))
            ->line('Country: '.(filled($submission->country) ? $this->escape($submission->country) : 'Not given'));

        if (filled($submission->message)) {
            $message->line('Message: '.$this->escape($submission->message));
        }

        return $message
            ->line('Rights confirmed: yes')
            ->action('Review in the admin', DemoSubmissionResource::getUrl('edit', ['record' => $submission]))
            ->salutation('Reply to this email to answer the artist.');
    }

    /**
     * The lines are rendered as Markdown: the artist's text must not turn into
     * links or formatting in the label's inbox.
     */
    private function escape(string $text): string
    {
        return (string) preg_replace('/([\\\\`*_\[\]<>#|!])/', '\\\\$1', $text);
    }
}

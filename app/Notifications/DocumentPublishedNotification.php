<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $siteName,
        protected string $targetLocale,
        protected string $title,
        protected ?string $summary,
        protected ?string $fileType,
        protected ?string $documentDate,
        protected string $url,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->siteName}: {$this->title}")
            ->markdown('mail.documents.published', [
                'siteName' => $this->siteName,
                'locale' => $this->targetLocale,
                'title' => $this->title,
                'summary' => $this->summary,
                'fileType' => $this->fileType,
                'documentDate' => $this->documentDate,
                'url' => $this->url,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'locale' => $this->targetLocale,
            'title' => $this->title,
            'summary' => $this->summary,
            'file_type' => $this->fileType,
            'document_date' => $this->documentDate,
            'url' => $this->url,
        ];
    }
}

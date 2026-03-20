<?php

namespace App\Observers;

use App\Actions\Subscriptions\DeliverPublicationNotifications;
use App\Models\Document;
use App\Models\Setting;
use App\Notifications\DocumentPublishedNotification;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class DocumentObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        protected DeliverPublicationNotifications $deliverPublicationNotifications,
    ) {}

    public function created(Document $document): void
    {
        $this->notifySubscribers($document);
    }

    public function updated(Document $document): void
    {
        $this->notifySubscribers($document);
    }

    protected function notifySubscribers(Document $document): void
    {
        $document = $document->fresh(['translations']);

        if (! $document instanceof Document) {
            return;
        }

        if ($document->status !== 'published' || $document->subscription_notified_at !== null) {
            return;
        }

        if ($document->published_at !== null && $document->published_at->isFuture()) {
            return;
        }

        $siteName = Setting::for('site', 'name', config('app.name'));

        $sentCount = $this->deliverPublicationNotifications->handle(
            resolveTranslation: fn (string $locale) => $document->translation($locale),
            makeNotification: fn (string $locale, object $translation) => new DocumentPublishedNotification(
                siteName: $siteName,
                targetLocale: $locale,
                title: $translation->title,
                summary: $translation->summary,
                fileType: $document->file_type,
                documentDate: $document->document_date?->toDateString(),
                url: route('public.documents.show', ['locale' => $locale, 'slug' => $translation->slug]),
            ),
        );

        if ($sentCount === 0) {
            return;
        }

        Document::query()
            ->whereKey($document->id)
            ->update(['subscription_notified_at' => now()]);
    }
}

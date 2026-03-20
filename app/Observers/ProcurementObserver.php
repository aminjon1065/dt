<?php

namespace App\Observers;

use App\Actions\Subscriptions\DeliverPublicationNotifications;
use App\Models\Procurement;
use App\Models\Setting;
use App\Notifications\ProcurementPublishedNotification;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ProcurementObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        protected DeliverPublicationNotifications $deliverPublicationNotifications,
    ) {}

    public function created(Procurement $procurement): void
    {
        $this->notifySubscribers($procurement);
    }

    public function updated(Procurement $procurement): void
    {
        $this->notifySubscribers($procurement);
    }

    protected function notifySubscribers(Procurement $procurement): void
    {
        $procurement = $procurement->fresh(['translations']);

        if (! $procurement instanceof Procurement) {
            return;
        }

        if (! in_array($procurement->status, ['open', 'closed', 'awarded'], true)) {
            return;
        }

        if ($procurement->subscription_notified_at !== null) {
            return;
        }

        if ($procurement->published_at !== null && $procurement->published_at->isFuture()) {
            return;
        }

        $siteName = Setting::for('site', 'name', config('app.name'));

        $sentCount = $this->deliverPublicationNotifications->handle(
            resolveTranslation: fn (string $locale) => $procurement->translation($locale),
            makeNotification: fn (string $locale, object $translation) => new ProcurementPublishedNotification(
                siteName: $siteName,
                targetLocale: $locale,
                title: $translation->title,
                summary: $translation->summary,
                status: $procurement->status,
                referenceNumber: $procurement->reference_number,
                url: route('public.procurements.show', ['locale' => $locale, 'slug' => $translation->slug]),
            ),
        );

        if ($sentCount === 0) {
            return;
        }

        Procurement::query()
            ->whereKey($procurement->id)
            ->update(['subscription_notified_at' => now()]);
    }
}

<?php

namespace App\Observers;

use App\Actions\Subscriptions\DeliverPublicationNotifications;
use App\Models\News;
use App\Models\Setting;
use App\Notifications\NewsPublishedNotification;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class NewsObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        protected DeliverPublicationNotifications $deliverPublicationNotifications,
    ) {}

    public function created(News $news): void
    {
        $this->notifySubscribers($news);
    }

    public function updated(News $news): void
    {
        $this->notifySubscribers($news);
    }

    protected function notifySubscribers(News $news): void
    {
        $news = $news->fresh(['translations']);

        if (! $news instanceof News) {
            return;
        }

        if ($news->status !== 'published' || $news->subscription_notified_at !== null) {
            return;
        }

        if ($news->published_at !== null && $news->published_at->isFuture()) {
            return;
        }

        $siteName = Setting::for('site', 'name', config('app.name'));

        $sentCount = $this->deliverPublicationNotifications->handle(
            resolveTranslation: fn (string $locale) => $news->translation($locale),
            makeNotification: fn (string $locale, object $translation) => new NewsPublishedNotification(
                siteName: $siteName,
                targetLocale: $locale,
                title: $translation->title,
                summary: $translation->summary,
                url: route('public.news.show', ['locale' => $locale, 'slug' => $translation->slug]),
            ),
        );

        if ($sentCount === 0) {
            return;
        }

        News::query()
            ->whereKey($news->id)
            ->update(['subscription_notified_at' => now()]);
    }
}

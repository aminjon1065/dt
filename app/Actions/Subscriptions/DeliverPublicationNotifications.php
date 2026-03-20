<?php

namespace App\Actions\Subscriptions;

use App\Models\Subscription;
use Illuminate\Notifications\AnonymousNotifiable;

class DeliverPublicationNotifications
{
    public function handle(callable $resolveTranslation, callable $makeNotification): int
    {
        $subscriptions = Subscription::query()
            ->active()
            ->orderBy('id')
            ->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $notifiedSubscriptionIds = [];

        foreach ($subscriptions as $subscription) {
            $effectiveLocale = $resolveTranslation($subscription->locale) !== null
                ? $subscription->locale
                : 'en';

            $translation = $resolveTranslation($effectiveLocale);

            if ($translation === null) {
                continue;
            }

            $notifiable = new AnonymousNotifiable;
            $notifiable->route('mail', $subscription->email);
            $notifiable->notify($makeNotification($effectiveLocale, $translation, $subscription));

            $notifiedSubscriptionIds[] = $subscription->id;
        }

        if ($notifiedSubscriptionIds === []) {
            return 0;
        }

        Subscription::query()
            ->whereKey($notifiedSubscriptionIds)
            ->update(['last_notified_at' => now()]);

        return count($notifiedSubscriptionIds);
    }
}

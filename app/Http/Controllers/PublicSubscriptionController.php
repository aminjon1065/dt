<?php

namespace App\Http\Controllers;

use App\Http\Requests\Public\StorePublicSubscriptionRequest;
use App\Http\Requests\Public\StorePublicUnsubscriptionRequest;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PublicSubscriptionController extends Controller
{
    public function create(string $locale): Response
    {
        return Inertia::render('public/subscriptions/create', [
            'locale' => $locale,
            'site' => $this->siteData($locale),
            'navigation' => app(PublicPageController::class)->navigation($locale),
            'seo' => [
                'title' => 'Email subscriptions',
                'description' => 'Subscribe to official updates and announcements by email.',
                'canonical_url' => route('public.subscriptions.create', ['locale' => $locale]),
                'type' => 'website',
            ],
        ]);
    }

    public function store(StorePublicSubscriptionRequest $request, string $locale): RedirectResponse
    {
        $subscription = Subscription::query()->updateOrCreate(
            ['email' => $request->validated('email')],
            [
                'locale' => $request->validated('locale'),
                'status' => 'active',
                'source' => 'public-form',
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ],
        );

        AuditLog::query()->create([
            'user_id' => null,
            'event' => 'public-subscribed',
            'auditable_type' => $subscription->getMorphClass(),
            'auditable_id' => $subscription->id,
            'old_values' => null,
            'new_values' => $subscription->fresh()->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return to_route('public.subscriptions.thank-you', ['locale' => $locale]);
    }

    public function thankYou(string $locale): Response
    {
        return Inertia::render('public/subscriptions/thank-you', [
            'locale' => $locale,
            'site' => $this->siteData($locale),
            'navigation' => app(PublicPageController::class)->navigation($locale),
            'seo' => [
                'title' => 'Subscription confirmed',
                'description' => 'Your email subscription has been recorded.',
                'canonical_url' => route('public.subscriptions.thank-you', ['locale' => $locale]),
                'robots' => 'noindex,nofollow',
                'type' => 'website',
            ],
        ]);
    }

    public function unsubscribe(string $locale): Response
    {
        return Inertia::render('public/subscriptions/unsubscribe', [
            'locale' => $locale,
            'site' => $this->siteData($locale),
            'navigation' => app(PublicPageController::class)->navigation($locale),
            'seo' => [
                'title' => 'Unsubscribe from email updates',
                'description' => 'Stop receiving official updates by email.',
                'canonical_url' => route('public.subscriptions.unsubscribe', ['locale' => $locale]),
                'robots' => 'noindex,follow',
                'type' => 'website',
            ],
            'prefillEmail' => request('email'),
        ]);
    }

    public function unsubscribeStore(StorePublicUnsubscriptionRequest $request, string $locale): RedirectResponse
    {
        $subscription = Subscription::query()
            ->where('email', $request->validated('email'))
            ->firstOrFail();

        $oldValues = $subscription->toArray();

        $subscription->update([
            'locale' => $request->validated('locale'),
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        AuditLog::query()->create([
            'user_id' => null,
            'event' => 'public-unsubscribed',
            'auditable_type' => $subscription->getMorphClass(),
            'auditable_id' => $subscription->id,
            'old_values' => $oldValues,
            'new_values' => $subscription->fresh()->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return to_route('public.subscriptions.unsubscribe-thank-you', ['locale' => $locale]);
    }

    public function unsubscribeThankYou(string $locale): Response
    {
        return Inertia::render('public/subscriptions/unsubscribe-thank-you', [
            'locale' => $locale,
            'site' => $this->siteData($locale),
            'navigation' => app(PublicPageController::class)->navigation($locale),
            'seo' => [
                'title' => 'Subscription cancelled',
                'description' => 'You will no longer receive portal updates by email.',
                'canonical_url' => route('public.subscriptions.unsubscribe-thank-you', ['locale' => $locale]),
                'robots' => 'noindex,nofollow',
                'type' => 'website',
            ],
        ]);
    }

    protected function siteData(string $locale): array
    {
        return [
            'name' => Setting::for('site', 'name', config('app.name')),
            'tagline' => Setting::for('site', 'tagline'),
            'default_locale' => Setting::for('site', 'default_locale', 'en'),
            'contact_email' => Setting::for('contact', 'email'),
            'contact_phone' => Setting::for('contact', 'phone'),
            'contact_address' => Setting::for('contact', 'address'),
            'locale' => $locale,
        ];
    }
}

<?php

use App\Models\Subscription;
use Inertia\Testing\AssertableInertia;

it('renders the public subscription form', function () {
    $this->withoutVite();

    $this->get('/ru/subscribe')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/subscriptions/create')
            ->where('locale', 'ru'));
});

it('stores or reactivates a public subscription and redirects to thank-you', function () {
    $response = $this
        ->withoutMiddleware()
        ->post('/en/subscribe', [
            'email' => 'subscriber@example.com',
            'locale' => 'en',
        ]);

    $response->assertRedirect(route('public.subscriptions.thank-you', ['locale' => 'en']));

    expect(Subscription::query()->count())->toBe(1)
        ->and(Subscription::query()->first()?->status)->toBe('active')
        ->and(Subscription::query()->first()?->source)->toBe('public-form');
});

it('renders the public subscription thank-you page', function () {
    $this->withoutVite();

    $this->get('/tj/subscribe/thank-you')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/subscriptions/thank-you'));
});

it('renders the public unsubscribe form', function () {
    $this->withoutVite();

    $this->get('/en/unsubscribe?email=subscriber@example.com')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/subscriptions/unsubscribe')
            ->where('prefillEmail', 'subscriber@example.com'));
});

it('unsubscribes a public subscription and redirects to thank-you', function () {
    $subscription = Subscription::query()->create([
        'email' => 'subscriber@example.com',
        'locale' => 'en',
        'status' => 'active',
        'source' => 'public-form',
        'subscribed_at' => now(),
    ]);

    $response = $this
        ->withoutMiddleware()
        ->post('/en/unsubscribe', [
            'email' => 'subscriber@example.com',
            'locale' => 'en',
        ]);

    $response->assertRedirect(route('public.subscriptions.unsubscribe-thank-you', ['locale' => 'en']));

    expect($subscription->fresh()?->status)->toBe('unsubscribed')
        ->and($subscription->fresh()?->unsubscribed_at)->not->toBeNull();
});

it('renders the public unsubscribe thank-you page', function () {
    $this->withoutVite();

    $this->get('/ru/unsubscribe/thank-you')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/subscriptions/unsubscribe-thank-you'));
});

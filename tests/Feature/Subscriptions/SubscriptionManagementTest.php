<?php

use App\Models\Subscription;
use App\Models\User;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;

it('allows authorized users to view subscriptions', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->create([
        'name' => 'subscriptions.view',
        'guard_name' => 'web',
    ]));

    $this->actingAs($user)
        ->get(route('cms.subscriptions.index'))
        ->assertSuccessful();
});

it('updates a subscription through the cms route', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->create([
        'name' => 'subscriptions.update',
        'guard_name' => 'web',
    ]));

    $subscription = Subscription::query()->create([
        'email' => 'subscriber@example.com',
        'locale' => 'en',
        'status' => 'active',
        'source' => 'public-form',
        'subscribed_at' => now(),
    ]);

    $response = $this
        ->withoutMiddleware()
        ->actingAs($user)
        ->put(route('cms.subscriptions.update', $subscription), [
            'email' => 'subscriber@example.com',
            'locale' => 'ru',
            'status' => 'unsubscribed',
            'source' => 'admin-review',
            'subscribed_at' => now()->subDay()->format('Y-m-d H:i:s'),
            'unsubscribed_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Manual unsubscribe',
        ]);

    $response->assertRedirect();

    expect($subscription->fresh()->status)->toBe('unsubscribed')
        ->and($subscription->fresh()->locale)->toBe('ru')
        ->and($subscription->fresh()->notes)->toBe('Manual unsubscribe');
});

it('filters subscriptions in the cms index', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'subscriptions.view',
        'guard_name' => 'web',
    ]));

    Subscription::query()->create([
        'email' => 'active@example.com',
        'locale' => 'en',
        'status' => 'active',
        'source' => 'public-form',
        'subscribed_at' => now(),
    ]);

    Subscription::query()->create([
        'email' => 'archived@example.com',
        'locale' => 'ru',
        'status' => 'unsubscribed',
        'source' => 'public-form',
        'subscribed_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('cms.subscriptions.index', ['search' => 'active', 'status' => 'active', 'locale' => 'en']))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('cms/subscriptions/index')
            ->where('filters.search', 'active')
            ->where('filters.status', 'active')
            ->where('filters.locale', 'en')
            ->has('subscriptions', 1)
            ->where('subscriptions.0.email', 'active@example.com'));
});

it('allows operators to move subscriptions through workflow actions', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'subscriptions.update',
        'guard_name' => 'web',
    ]));

    $subscription = Subscription::query()->create([
        'email' => 'subscriber@example.com',
        'locale' => 'en',
        'status' => 'active',
        'source' => 'public-form',
        'subscribed_at' => now(),
    ]);

    $this->withoutMiddleware()
        ->actingAs($user)
        ->post(route('cms.subscriptions.workflow', $subscription), [
            'status' => 'unsubscribed',
        ])
        ->assertRedirect();

    expect($subscription->fresh()->status)->toBe('unsubscribed')
        ->and($subscription->fresh()->unsubscribed_at)->not->toBeNull();
});

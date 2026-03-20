<?php

use App\Models\AuditLog;
use App\Models\News;
use App\Models\Subscription;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('renders the audit log index with filters', function () {
    $this->withoutVite();

    $user = User::factory()->create([
        'name' => 'Audit Admin',
        'email' => 'audit@example.test',
    ]);

    AuditLog::query()->create([
        'user_id' => $user->id,
        'event' => 'workflow-updated',
        'auditable_type' => News::class,
        'auditable_id' => 101,
        'old_values' => ['status' => 'draft'],
        'new_values' => ['status' => 'published', 'published_at' => now()->toISOString()],
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'created_at' => now(),
    ]);

    AuditLog::query()->create([
        'user_id' => null,
        'event' => 'public-subscribed',
        'auditable_type' => Subscription::class,
        'auditable_id' => 202,
        'old_values' => null,
        'new_values' => ['status' => 'active'],
        'ip_address' => '127.0.0.2',
        'user_agent' => 'Browser',
        'created_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('cms.audit-logs.index', [
            'event' => 'workflow-updated',
            'model' => 'news',
            'actor' => 'Audit',
        ]))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('cms/audit-logs/index')
            ->where('filters.event', 'workflow-updated')
            ->where('filters.model', 'news')
            ->where('filters.actor', 'Audit')
            ->where('stats.total', 2)
            ->where('stats.public_actions', 1)
            ->where('stats.admin_actions', 1)
            ->has('logs', 1)
            ->where('logs.0.model', 'News')
            ->where('logs.0.event', 'workflow-updated'));
});

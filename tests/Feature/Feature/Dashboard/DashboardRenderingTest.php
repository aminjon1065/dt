<?php

use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\GrmSubmission;
use App\Models\News;
use App\Models\Page;
use App\Models\Procurement;
use App\Models\StaffMember;
use App\Models\Subscription;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('renders the cms dashboard with module stats and filtered activity', function () {
    $this->withoutVite();

    $user = User::factory()->create([
        'name' => 'Dashboard Editor',
        'email' => 'editor@example.test',
    ]);

    Page::query()->create([
        'template' => 'default',
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    News::query()->create([
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $category = DocumentCategory::query()->create([
        'slug' => 'reports',
        'is_active' => true,
    ]);

    Document::query()->create([
        'document_category_id' => $category->id,
        'status' => 'published',
        'file_type' => 'pdf',
        'document_date' => now()->toDateString(),
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    Procurement::query()->create([
        'reference_number' => 'RFQ-2026-001',
        'procurement_type' => 'rfq',
        'status' => 'open',
        'published_at' => now(),
        'closing_at' => now()->addWeek(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    GrmSubmission::query()->create([
        'status' => 'new',
        'name' => 'Citizen',
        'email' => 'citizen@example.test',
        'subject' => 'Need support',
        'message' => 'Need support',
        'reference_number' => 'GRM-2026-0001',
        'submitted_at' => now(),
    ]);

    StaffMember::query()->create([
        'status' => 'published',
        'published_at' => now(),
        'email' => 'staff@example.test',
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    Subscription::query()->create([
        'email' => 'subscriber@example.test',
        'locale' => 'ru',
        'status' => 'active',
        'source' => 'public-form',
        'subscribed_at' => now(),
    ]);

    AuditLog::query()->create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => News::class,
        'auditable_id' => 101,
        'old_values' => null,
        'new_values' => ['status' => 'published'],
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
        'user_agent' => 'Pest',
        'created_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard', ['event' => 'created', 'model' => 'news', 'actor' => 'Dashboard']))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('dashboard')
            ->where('stats.published_pages', 1)
            ->where('stats.published_news', 1)
            ->where('stats.published_documents', 1)
            ->where('stats.active_procurements', 1)
            ->where('stats.new_grm_submissions', 1)
            ->where('stats.published_staff', 1)
            ->where('stats.active_subscriptions', 1)
            ->where('filters.event', 'created')
            ->where('filters.model', 'news')
            ->where('filters.actor', 'Dashboard')
            ->has('filterOptions.events', 5)
            ->has('filterOptions.models', 7)
            ->has('activity', 1)
            ->where('activity.0.model', 'News')
            ->where('activity.0.event', 'created')
            ->where('activity.0.actor.name', 'Dashboard Editor'));
});

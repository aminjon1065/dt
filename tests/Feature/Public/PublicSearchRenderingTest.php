<?php

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\News;
use App\Models\Page;
use App\Models\Procurement;
use App\Models\StaffMember;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('renders the public search page without results when query is empty', function () {
    $this->withoutVite();

    $this->get('/en/search')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/search/index')
            ->where('filters.q', null)
            ->where('results.pages', [])
            ->where('results.news', []));
});

it('renders grouped public search results across content modules', function () {
    $this->withoutVite();

    $user = User::factory()->create();

    $page = Page::query()->create([
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $page->translations()->create([
        'locale' => 'en',
        'title' => 'Climate overview',
        'slug' => 'climate-overview',
        'summary' => 'Climate summary',
        'content' => '<p>Climate content</p>',
    ]);

    $news = News::query()->create([
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $news->translations()->create([
        'locale' => 'en',
        'title' => 'Climate launch update',
        'slug' => 'climate-launch-update',
        'summary' => 'News summary',
        'content' => '<p>News content</p>',
    ]);

    $category = DocumentCategory::query()->create([
        'slug' => 'reports',
        'is_active' => true,
    ]);
    $category->translations()->create([
        'locale' => 'en',
        'name' => 'Reports',
    ]);

    $document = Document::query()->create([
        'document_category_id' => $category->id,
        'status' => 'published',
        'file_type' => 'pdf',
        'document_date' => now()->toDateString(),
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $document->translations()->create([
        'locale' => 'en',
        'title' => 'Climate report',
        'slug' => 'climate-report',
        'summary' => 'Document summary',
    ]);

    $procurement = Procurement::query()->create([
        'reference_number' => 'RFQ-CLIMATE-001',
        'procurement_type' => 'services',
        'status' => 'open',
        'published_at' => now(),
        'closing_at' => now()->addWeek(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $procurement->translations()->create([
        'locale' => 'en',
        'title' => 'Climate consulting procurement',
        'slug' => 'climate-consulting-procurement',
        'summary' => 'Procurement summary',
        'content' => '<p>Procurement content</p>',
    ]);

    $staffMember = StaffMember::query()->create([
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $staffMember->translations()->create([
        'locale' => 'en',
        'name' => 'Climate officer',
        'slug' => 'climate-officer',
        'position' => 'Climate specialist',
        'bio' => '<p>Staff profile</p>',
    ]);

    $this->get('/en/search?q=Climate')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/search/index')
            ->where('filters.q', 'Climate')
            ->where('results.pages.0.title', 'Climate overview')
            ->where('results.news.0.title', 'Climate launch update')
            ->where('results.documents.0.title', 'Climate report')
            ->where('results.procurements.0.title', 'Climate consulting procurement')
            ->where('results.staff.0.title', 'Climate officer'));
});

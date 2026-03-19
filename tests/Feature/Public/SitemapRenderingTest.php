<?php

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\News;
use App\Models\Page;
use App\Models\Procurement;
use App\Models\User;

it('renders an xml sitemap with published public routes', function () {
    $user = User::factory()->create();

    $page = Page::query()->create([
        'template' => 'default',
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $page->translations()->create([
        'locale' => 'en',
        'title' => 'About',
        'slug' => 'about',
    ]);

    $news = News::query()->create([
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $news->translations()->create([
        'locale' => 'en',
        'title' => 'Portal update',
        'slug' => 'portal-update',
    ]);

    $category = DocumentCategory::query()->create([
        'slug' => 'policies',
        'is_active' => true,
    ]);

    $category->translations()->create([
        'locale' => 'en',
        'name' => 'Policies',
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
        'title' => 'Policy paper',
        'slug' => 'policy-paper',
    ]);

    $procurement = Procurement::query()->create([
        'reference_number' => 'RFQ-2026-002',
        'procurement_type' => 'rfq',
        'status' => 'open',
        'published_at' => now(),
        'closing_at' => now()->addWeek(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $procurement->translations()->create([
        'locale' => 'en',
        'title' => 'Office equipment',
        'slug' => 'office-equipment',
    ]);

    $response = $this->get(route('sitemap'));

    $response
        ->assertSuccessful()
        ->assertHeader('content-type', 'application/xml')
        ->assertSee(route('public.home', ['locale' => 'en']), false)
        ->assertSee(route('public.pages.show', ['locale' => 'en', 'slug' => 'about']), false)
        ->assertSee(route('public.news.show', ['locale' => 'en', 'slug' => 'portal-update']), false)
        ->assertSee(route('public.documents.show', ['locale' => 'en', 'slug' => 'policy-paper']), false)
        ->assertSee(route('public.procurements.show', ['locale' => 'en', 'slug' => 'office-equipment']), false);
});

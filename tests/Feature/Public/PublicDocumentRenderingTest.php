<?php

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentTag;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('renders the public documents archive', function () {
    $user = User::factory()->create();
    $category = DocumentCategory::query()->create([
        'slug' => 'reports',
        'is_active' => true,
    ]);
    $category->translations()->create([
        'locale' => 'en',
        'name' => 'Reports',
    ]);
    $category->translations()->create([
        'locale' => 'ru',
        'name' => 'Отчеты',
    ]);

    $tag = DocumentTag::query()->create([
        'slug' => 'featured',
    ]);
    $tag->translations()->create([
        'locale' => 'ru',
        'name' => 'Избранное',
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
    $document->translations()->createMany([
        [
            'locale' => 'en',
            'title' => 'Annual report',
            'slug' => 'annual-report',
            'summary' => 'Summary',
        ],
        [
            'locale' => 'ru',
            'title' => 'Годовой отчет',
            'slug' => 'godovoj-otchet',
            'summary' => 'Кратко',
        ],
    ]);
    $document->tags()->attach($tag);

    $this->get('/ru/documents')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/documents/index')
            ->where('documents.data.0.title', 'Годовой отчет'));
});

it('filters the public documents archive by query parameters', function () {
    $user = User::factory()->create();

    $reportsCategory = DocumentCategory::query()->create([
        'slug' => 'reports',
        'is_active' => true,
    ]);
    $reportsCategory->translations()->create([
        'locale' => 'en',
        'name' => 'Reports',
    ]);

    $policiesCategory = DocumentCategory::query()->create([
        'slug' => 'policies',
        'is_active' => true,
    ]);
    $policiesCategory->translations()->create([
        'locale' => 'en',
        'name' => 'Policies',
    ]);

    $featuredTag = DocumentTag::query()->create(['slug' => 'featured']);
    $featuredTag->translations()->create([
        'locale' => 'en',
        'name' => 'Featured',
    ]);

    $matchingDocument = Document::query()->create([
        'document_category_id' => $reportsCategory->id,
        'status' => 'published',
        'file_type' => 'pdf',
        'document_date' => now()->toDateString(),
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $matchingDocument->translations()->create([
        'locale' => 'en',
        'title' => 'Annual report',
        'slug' => 'annual-report',
        'summary' => 'Featured summary',
    ]);
    $matchingDocument->tags()->attach($featuredTag);

    $nonMatchingDocument = Document::query()->create([
        'document_category_id' => $policiesCategory->id,
        'status' => 'published',
        'file_type' => 'docx',
        'document_date' => now()->toDateString(),
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $nonMatchingDocument->translations()->create([
        'locale' => 'en',
        'title' => 'Internal policy',
        'slug' => 'internal-policy',
        'summary' => 'Operations guide',
    ]);

    $this->get('/en/documents?search=Annual&category=reports&tag=featured&file_type=pdf')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/documents/index')
            ->where('filters.search', 'Annual')
            ->where('filters.category', 'reports')
            ->where('filters.tag', 'featured')
            ->where('filters.file_type', 'pdf')
            ->has('documents.data', 1)
            ->where('documents.data.0.title', 'Annual report'));
});

it('renders a public document detail page by localized slug', function () {
    $user = User::factory()->create();
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
    $document->translations()->createMany([
        [
            'locale' => 'en',
            'title' => 'Project policy',
            'slug' => 'project-policy',
            'summary' => 'Summary',
            'content' => '<p>Policy body</p>',
            'seo_title' => 'Project policy SEO',
        ],
        [
            'locale' => 'tj',
            'title' => 'Сиёсати лоиҳа',
            'slug' => 'siyosati-loiha',
            'summary' => 'Хулоса',
            'content' => '<p>Тавсифи ҳуҷҷат</p>',
            'seo_title' => 'SEO',
        ],
    ]);

    $this->get('/tj/documents/siyosati-loiha')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/documents/show')
            ->where('document.title', 'Сиёсати лоиҳа')
            ->where('document.content', '<p>Тавсифи ҳуҷҷат</p>'));
});

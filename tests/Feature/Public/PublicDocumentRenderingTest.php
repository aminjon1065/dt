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
            ->where('documents.0.title', 'Годовой отчет'));
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
        ],
        [
            'locale' => 'tj',
            'title' => 'Сиёсати лоиҳа',
            'slug' => 'siyosati-loiha',
            'summary' => 'Хулоса',
        ],
    ]);

    $this->get('/tj/documents/siyosati-loiha')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/documents/show')
            ->where('document.title', 'Сиёсати лоиҳа'));
});

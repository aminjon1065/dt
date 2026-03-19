<?php

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\News;
use App\Models\Page;
use App\Models\Procurement;
use App\Models\Setting;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('redirects root to the default locale homepage', function () {
    $this->withoutVite();

    Setting::query()->create([
        'group' => 'site',
        'key' => 'default_locale',
        'type' => 'string',
        'value' => 'ru',
    ]);

    $this->get('/')->assertRedirect(route('public.home', ['locale' => 'ru']));
});

it('renders the published localized homepage', function () {
    $this->withoutVite();

    $user = User::factory()->create();

    $page = Page::query()->create([
        'template' => 'homepage',
        'status' => 'published',
        'published_at' => now(),
        'is_home' => true,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $page->translations()->createMany([
        [
            'locale' => 'en',
            'title' => 'Home',
            'slug' => 'home',
            'summary' => 'Homepage summary',
            'content' => '<p>Homepage content</p>',
        ],
        [
            'locale' => 'ru',
            'title' => 'Главная',
            'slug' => 'glavnaya',
            'summary' => 'Главная страница',
            'content' => '<p>Контент</p>',
        ],
    ]);

    $news = News::query()->create([
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $news->translations()->create([
        'locale' => 'ru',
        'title' => 'Последние новости',
        'slug' => 'poslednie-novosti',
        'summary' => 'Краткое описание новости',
    ]);

    $category = DocumentCategory::query()->create([
        'slug' => 'reports',
        'is_active' => true,
    ]);

    $category->translations()->create([
        'locale' => 'ru',
        'name' => 'Отчеты',
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
        'locale' => 'ru',
        'title' => 'Годовой отчет',
        'slug' => 'godovoi-otchet',
        'summary' => 'Описание отчета',
    ]);

    $procurement = Procurement::query()->create([
        'reference_number' => 'RFQ-2026-001',
        'procurement_type' => 'rfq',
        'status' => 'open',
        'published_at' => now(),
        'closing_at' => now()->addWeek(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $procurement->translations()->create([
        'locale' => 'ru',
        'title' => 'Закупка оборудования',
        'slug' => 'zakupka-oborudovaniya',
        'summary' => 'Описание закупки',
    ]);

    $this->get('/ru')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/home')
            ->where('page.title', 'Главная')
            ->where('seo.canonical_url', route('public.home', ['locale' => 'ru']))
            ->where('latestNews.0.title', 'Последние новости')
            ->where('latestDocuments.0.title', 'Годовой отчет')
            ->where('latestProcurements.0.title', 'Закупка оборудования'));
});

it('renders a published localized page by slug', function () {
    $this->withoutVite();

    $user = User::factory()->create();

    $page = Page::query()->create([
        'template' => 'default',
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $page->translations()->createMany([
        [
            'locale' => 'en',
            'title' => 'About Us',
            'slug' => 'about-us',
            'summary' => 'About summary',
            'content' => '<p>About content</p>',
        ],
        [
            'locale' => 'tj',
            'title' => 'Дар бораи мо',
            'slug' => 'dar-borai-mo',
            'summary' => 'Хулоса',
            'content' => '<p>Матн</p>',
        ],
    ]);

    $this->get('/tj/dar-borai-mo')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/page')
            ->where('page.title', 'Дар бораи мо')
            ->where('seo.canonical_url', route('public.pages.show', ['locale' => 'tj', 'slug' => 'dar-borai-mo'])));
});

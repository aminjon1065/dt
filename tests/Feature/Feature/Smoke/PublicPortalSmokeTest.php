<?php

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\News;
use App\Models\Page;
use App\Models\Procurement;
use App\Models\Setting;
use App\Models\StaffMember;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('smoke tests the key public portal routes', function () {
    $this->withoutVite();

    Setting::query()->create([
        'group' => 'site',
        'key' => 'default_locale',
        'type' => 'string',
        'value' => 'ru',
    ]);

    $user = User::factory()->create();

    $homePage = Page::query()->create([
        'template' => 'homepage',
        'status' => 'published',
        'published_at' => now(),
        'is_home' => true,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $homePage->translations()->create([
        'locale' => 'ru',
        'title' => 'Главная',
        'slug' => 'glavnaya',
        'summary' => 'Главная страница портала',
        'content' => '<p>Контент главной страницы</p>',
    ]);

    $contentPage = Page::query()->create([
        'template' => 'default',
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $contentPage->translations()->create([
        'locale' => 'ru',
        'title' => 'О проекте',
        'slug' => 'o-proekte',
        'summary' => 'О проекте',
        'content' => '<p>Описание проекта</p>',
    ]);

    $news = News::query()->create([
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $news->translations()->create([
        'locale' => 'ru',
        'title' => 'Обновление портала',
        'slug' => 'obnovlenie-portala',
        'summary' => 'Краткая новость',
        'content' => 'Полный текст новости',
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
        'summary' => 'Публичный документ',
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
        'summary' => 'Публичная закупка',
        'content' => 'Описание закупки',
    ]);

    $staffMember = StaffMember::query()->create([
        'status' => 'published',
        'published_at' => now(),
        'sort_order' => 1,
        'show_email_publicly' => true,
        'show_phone_publicly' => true,
        'email' => 'staff@example.test',
        'phone' => '+992900000001',
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $staffMember->translations()->create([
        'locale' => 'ru',
        'name' => 'Иван Иванов',
        'slug' => 'ivan-ivanov',
        'position' => 'Руководитель отдела',
        'bio' => 'Профиль сотрудника',
    ]);

    $this->get('/')
        ->assertRedirect(route('public.home', ['locale' => 'ru']));

    $this->get(route('public.home', ['locale' => 'ru']))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/home')
            ->where('page.title', 'Главная'));

    $this->get(route('public.pages.show', ['locale' => 'ru', 'slug' => 'o-proekte']))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/page')
            ->where('page.slug', 'o-proekte'));

    $this->get(route('public.news.index', ['locale' => 'ru']))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/news/index')
            ->has('newsItems.data', 1));

    $this->get(route('public.documents.index', ['locale' => 'ru']))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/documents/index')
            ->has('documents.data', 1));

    $this->get(route('public.procurements.index', ['locale' => 'ru']))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/procurements/index')
            ->has('procurements.data', 1));

    $this->get(route('public.staff.index', ['locale' => 'ru']))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/staff/index')
            ->has('staffMembers', 1));

    $this->get(route('public.grm.create', ['locale' => 'ru']))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/grm/create'));

    $this->get(route('public.subscriptions.create', ['locale' => 'ru']))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/subscriptions/create'));

    $this->get(route('public.search', ['locale' => 'ru', 'q' => 'портала']))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/search/index')
            ->where('filters.q', 'портала')
            ->has('results.news', 1));
});

<?php

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('renders the public news listing', function () {
    $user = User::factory()->create();
    $category = NewsCategory::query()->create([
        'slug' => 'announcements',
        'is_active' => true,
    ]);
    $category->translations()->create([
        'locale' => 'en',
        'name' => 'Announcements',
    ]);
    $category->translations()->create([
        'locale' => 'ru',
        'name' => 'Объявления',
    ]);

    $news = News::query()->create([
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $news->translations()->createMany([
        [
            'locale' => 'en',
            'title' => 'Project launched',
            'slug' => 'project-launched',
            'summary' => 'Launch summary',
            'content' => '<p>Launch content</p>',
        ],
        [
            'locale' => 'ru',
            'title' => 'Проект запущен',
            'slug' => 'proekt-zapushen',
            'summary' => 'Кратко',
            'content' => '<p>Контент</p>',
        ],
    ]);
    $news->categories()->attach($category);

    $this->get('/ru/news')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/news/index')
            ->where('newsItems.data.0.title', 'Проект запущен'));
});

it('filters the public news listing by search and category', function () {
    $user = User::factory()->create();

    $announcements = NewsCategory::query()->create([
        'slug' => 'announcements',
        'is_active' => true,
    ]);
    $announcements->translations()->create([
        'locale' => 'en',
        'name' => 'Announcements',
    ]);

    $updates = NewsCategory::query()->create([
        'slug' => 'updates',
        'is_active' => true,
    ]);
    $updates->translations()->create([
        'locale' => 'en',
        'name' => 'Updates',
    ]);

    $matchingNews = News::query()->create([
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $matchingNews->translations()->create([
        'locale' => 'en',
        'title' => 'Project launch announcement',
        'slug' => 'project-launch-announcement',
        'summary' => 'Launch summary',
        'content' => '<p>Launch content</p>',
    ]);
    $matchingNews->categories()->attach($announcements);

    $nonMatchingNews = News::query()->create([
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $nonMatchingNews->translations()->create([
        'locale' => 'en',
        'title' => 'Weekly operations update',
        'slug' => 'weekly-operations-update',
        'summary' => 'Operations summary',
        'content' => '<p>Operations content</p>',
    ]);
    $nonMatchingNews->categories()->attach($updates);

    $this->get('/en/news?search=launch&category=announcements')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/news/index')
            ->where('filters.search', 'launch')
            ->where('filters.category', 'announcements')
            ->has('newsItems.data', 1)
            ->where('newsItems.data.0.title', 'Project launch announcement'));
});

it('renders a public news detail page by localized slug', function () {
    $user = User::factory()->create();
    $news = News::query()->create([
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $news->translations()->createMany([
        [
            'locale' => 'en',
            'title' => 'Launch update',
            'slug' => 'launch-update',
            'summary' => 'Summary',
            'content' => '<p>Content</p>',
        ],
        [
            'locale' => 'tj',
            'title' => 'Навсозии оғоз',
            'slug' => 'navsozii-ogoz',
            'summary' => 'Хулоса',
            'content' => '<p>Матн</p>',
        ],
    ]);

    $this->get('/tj/news/navsozii-ogoz')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/news/show')
            ->where('newsItem.title', 'Навсозии оғоз'));
});

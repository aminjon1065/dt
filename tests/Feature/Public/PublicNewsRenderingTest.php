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
            ->where('newsItems.0.title', 'Проект запущен'));
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

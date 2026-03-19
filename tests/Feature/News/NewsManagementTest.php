<?php

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use Spatie\Permission\Models\Permission;

it('supports localized news and categories', function () {
    $user = User::factory()->create();
    $category = NewsCategory::query()->create([
        'slug' => 'announcements',
        'is_active' => true,
    ]);
    $category->translations()->create([
        'locale' => 'en',
        'name' => 'Announcements',
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
            'content' => 'Launch content',
        ],
        [
            'locale' => 'ru',
            'title' => 'Проект запущен',
            'slug' => 'proekt-zapushen',
            'summary' => 'Кратко',
            'content' => 'Контент',
        ],
    ]);

    $news->categories()->attach($category);

    expect($news->translation('en')?->title)->toBe('Project launched')
        ->and($news->categories)->toHaveCount(1)
        ->and($news->categories->first()?->translation('en')?->name)->toBe('Announcements');
});

it('allows authorized users to view the news index', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->create([
        'name' => 'news.view',
        'guard_name' => 'web',
    ]));

    $this->actingAs($user)
        ->get(route('cms.news.index'))
        ->assertSuccessful();
});

it('stores a news item and translations through the cms route', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->create([
        'name' => 'news.create',
        'guard_name' => 'web',
    ]));

    $category = NewsCategory::query()->create([
        'slug' => 'updates',
        'is_active' => true,
    ]);
    $category->translations()->create([
        'locale' => 'en',
        'name' => 'Updates',
    ]);

    $response = $this
        ->withoutMiddleware()
        ->actingAs($user)
        ->post(route('cms.news.store'), [
            'status' => 'draft',
            'category_ids' => [$category->id],
            'translations' => [
                'en' => [
                    'title' => 'Launch update',
                    'slug' => 'launch-update',
                    'summary' => 'Summary',
                    'content' => 'Content',
                ],
                'tj' => [
                    'title' => 'Навсозии оғоз',
                    'slug' => 'navsozii-ogoz',
                    'summary' => 'Хулоса',
                    'content' => 'Матн',
                ],
                'ru' => [
                    'title' => 'Обновление запуска',
                    'slug' => 'obnovlenie-zapuska',
                    'summary' => 'Кратко',
                    'content' => 'Контент',
                ],
            ],
        ]);

    $response->assertRedirect();

    expect(News::query()->count())->toBe(1)
        ->and(News::query()->first()?->translations()->count())->toBe(3)
        ->and(News::query()->first()?->categories()->count())->toBe(1);
});

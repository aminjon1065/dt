<?php

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\NewsPublishedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
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

it('filters news by workflow status in the cms index', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'news.view',
        'guard_name' => 'web',
    ]));

    $draftNews = News::query()->create([
        'status' => 'draft',
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $draftNews->translations()->create([
        'locale' => 'en',
        'title' => 'Draft news',
        'slug' => 'draft-news',
    ]);

    $publishedNews = News::query()->create([
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $publishedNews->translations()->create([
        'locale' => 'en',
        'title' => 'Published news',
        'slug' => 'published-news',
    ]);

    $this->actingAs($user)
        ->get(route('cms.news.index', ['status' => 'draft']))
        ->assertSuccessful()
        ->assertSee('Draft news')
        ->assertDontSee('Published news');
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
                    'content_blocks' => json_encode([
                        ['id' => 'en-heading', 'type' => 'heading', 'content' => 'Launch update', 'level' => 2],
                        ['id' => 'en-paragraph', 'type' => 'paragraph', 'content' => 'Content'],
                    ], JSON_THROW_ON_ERROR),
                ],
                'tj' => [
                    'title' => 'Навсозии оғоз',
                    'slug' => 'navsozii-ogoz',
                    'summary' => 'Хулоса',
                    'content_blocks' => json_encode([
                        ['id' => 'tj-quote', 'type' => 'quote', 'content' => 'Матн'],
                    ], JSON_THROW_ON_ERROR),
                ],
                'ru' => [
                    'title' => 'Обновление запуска',
                    'slug' => 'obnovlenie-zapuska',
                    'summary' => 'Кратко',
                    'content_blocks' => json_encode([
                        ['id' => 'ru-list', 'type' => 'list', 'items' => ['Пункт 1', 'Пункт 2']],
                    ], JSON_THROW_ON_ERROR),
                ],
            ],
        ]);

    $response->assertRedirect();

    expect(News::query()->count())->toBe(1)
        ->and(News::query()->first()?->translations()->count())->toBe(3)
        ->and(News::query()->first()?->categories()->count())->toBe(1)
        ->and(News::query()->first()?->translation('en')?->content)->toContain('<h2>Launch update</h2>')
        ->and(News::query()->first()?->translation('tj')?->content)->toContain('<blockquote><p>Матн</p></blockquote>')
        ->and(News::query()->first()?->translation('ru')?->content_blocks)->toHaveCount(1);
});

it('does not allow contributors without publish permission to publish news', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'news.create',
        'guard_name' => 'web',
    ]));

    $this->withoutMiddleware()
        ->actingAs($user)
        ->post(route('cms.news.store'), [
            'status' => 'published',
            'translations' => [
                'en' => ['title' => 'Launch update', 'slug' => 'launch-update'],
                'tj' => ['title' => 'Навсозии оғоз', 'slug' => 'navsozii-ogoz'],
                'ru' => ['title' => 'Обновление запуска', 'slug' => 'obnovlenie-zapuska'],
            ],
        ])
        ->assertRedirect();

    expect(News::query()->count())->toBe(0);
});

it('allows contributors to send news to review through workflow actions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'news.update',
        'guard_name' => 'web',
    ]));

    $news = News::query()->create([
        'status' => 'draft',
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $news->translations()->create([
        'locale' => 'en',
        'title' => 'Workflow news',
        'slug' => 'workflow-news',
    ]);

    $this->withoutMiddleware()
        ->actingAs($user)
        ->post(route('cms.news.workflow', $news), [
            'status' => 'in_review',
        ])
        ->assertRedirect();

    expect($news->fresh()->status)->toBe('in_review');
});

it('allows editors to replace or remove the current news cover', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'news.update',
        'guard_name' => 'web',
    ]));

    $news = News::query()->create([
        'status' => 'draft',
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $news->translations()->createMany([
        ['locale' => 'en', 'title' => 'Launch update', 'slug' => 'launch-update'],
        ['locale' => 'tj', 'title' => 'Навсозии оғоз', 'slug' => 'navsozii-ogoz'],
        ['locale' => 'ru', 'title' => 'Обновление запуска', 'slug' => 'obnovlenie-zapuska'],
    ]);
    $news->addMedia(UploadedFile::fake()->image('old-cover.jpg'))
        ->toMediaCollection('cover');

    $token = 'news-update-token';

    $this->withSession(['_token' => $token])
        ->actingAs($user)
        ->put(route('cms.news.update', $news), [
            '_token' => $token,
            'status' => 'draft',
            'cover' => UploadedFile::fake()->image('new-cover.jpg'),
            'translations' => [
                'en' => ['title' => 'Launch update', 'slug' => 'launch-update'],
                'tj' => ['title' => 'Навсозии оғоз', 'slug' => 'navsozii-ogoz'],
                'ru' => ['title' => 'Обновление запуска', 'slug' => 'obnovlenie-zapuska'],
            ],
        ])
        ->assertRedirect();

    expect($news->fresh()->getMedia('cover'))->toHaveCount(1)
        ->and($news->fresh()->getFirstMedia('cover')?->name)->toBe('new-cover');

    $this->withSession(['_token' => $token])
        ->actingAs($user)
        ->put(route('cms.news.update', $news), [
            '_token' => $token,
            'status' => 'draft',
            'remove_cover' => '1',
            'translations' => [
                'en' => ['title' => 'Launch update', 'slug' => 'launch-update'],
                'tj' => ['title' => 'Навсозии оғоз', 'slug' => 'navsozii-ogoz'],
                'ru' => ['title' => 'Обновление запуска', 'slug' => 'obnovlenie-zapuska'],
            ],
        ])
        ->assertRedirect();

    expect($news->fresh()->getMedia('cover'))->toHaveCount(0);
});

it('sends email notifications to active subscriptions when a news item is published', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::query()->firstOrCreate([
            'name' => 'news.create',
            'guard_name' => 'web',
        ]),
        Permission::query()->firstOrCreate([
            'name' => 'news.publish',
            'guard_name' => 'web',
        ]),
    ]);

    $category = NewsCategory::query()->create([
        'slug' => 'updates',
        'is_active' => true,
    ]);
    $category->translations()->create([
        'locale' => 'en',
        'name' => 'Updates',
    ]);

    $activeSubscription = Subscription::query()->create([
        'email' => 'active@example.com',
        'locale' => 'en',
        'status' => 'active',
        'subscribed_at' => now(),
    ]);

    Subscription::query()->create([
        'email' => 'inactive@example.com',
        'locale' => 'en',
        'status' => 'unsubscribed',
        'subscribed_at' => now()->subDay(),
        'unsubscribed_at' => now(),
    ]);

    $response = $this
        ->withoutMiddleware()
        ->actingAs($user)
        ->post(route('cms.news.store'), [
            'status' => 'published',
            'published_at' => now()->format('Y-m-d H:i:s'),
            'category_ids' => [$category->id],
            'translations' => [
                'en' => [
                    'title' => 'Launch update',
                    'slug' => 'launch-update',
                    'summary' => 'Summary',
                    'content_blocks' => json_encode([
                        ['id' => 'en-paragraph', 'type' => 'paragraph', 'content' => 'Content'],
                    ], JSON_THROW_ON_ERROR),
                ],
                'tj' => [
                    'title' => 'Навсозии оғоз',
                    'slug' => 'navsozii-ogoz',
                    'summary' => 'Хулоса',
                    'content_blocks' => json_encode([
                        ['id' => 'tj-paragraph', 'type' => 'paragraph', 'content' => 'Матн'],
                    ], JSON_THROW_ON_ERROR),
                ],
                'ru' => [
                    'title' => 'Обновление запуска',
                    'slug' => 'obnovlenie-zapuska',
                    'summary' => 'Кратко',
                    'content_blocks' => json_encode([
                        ['id' => 'ru-paragraph', 'type' => 'paragraph', 'content' => 'Контент'],
                    ], JSON_THROW_ON_ERROR),
                ],
            ],
        ]);

    $response->assertRedirect();

    Notification::assertSentOnDemand(
        NewsPublishedNotification::class,
        function (NewsPublishedNotification $notification, array $channels, object $notifiable): bool {
            return in_array('mail', $channels, true)
                && $notifiable->routes['mail'] === 'active@example.com';
        },
    );

    $news = News::query()->first();

    expect($news?->subscription_notified_at)->not->toBeNull()
        ->and($activeSubscription->fresh()?->last_notified_at)->not->toBeNull();
});

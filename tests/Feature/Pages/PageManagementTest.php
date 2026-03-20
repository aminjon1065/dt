<?php

use App\Models\Page;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

it('supports hierarchical pages with localized translations', function () {
    $user = User::factory()->create();

    $parentPage = Page::query()->create([
        'template' => 'default',
        'status' => 'published',
        'published_at' => now(),
        'sort_order' => 1,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $parentPage->translations()->createMany([
        [
            'locale' => 'en',
            'title' => 'About Us',
            'slug' => 'about-us',
            'summary' => 'About summary',
            'content' => 'English content',
            'seo_title' => 'About Us',
            'seo_description' => 'About description',
        ],
        [
            'locale' => 'ru',
            'title' => 'О нас',
            'slug' => 'o-nas',
            'summary' => 'Краткое описание',
            'content' => 'Русский контент',
            'seo_title' => 'О нас',
            'seo_description' => 'Описание страницы',
        ],
    ]);

    $childPage = Page::query()->create([
        'parent_id' => $parentPage->id,
        'template' => 'default',
        'status' => 'draft',
        'sort_order' => 2,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $childPage->translations()->create([
        'locale' => 'en',
        'title' => 'Our Team',
        'slug' => 'our-team',
        'content' => 'Team content',
    ]);

    expect($parentPage->children)->toHaveCount(1)
        ->and($parentPage->children->first()->is($childPage))->toBeTrue()
        ->and($parentPage->translation('en')?->title)->toBe('About Us')
        ->and($parentPage->translation('ru')?->slug)->toBe('o-nas')
        ->and($childPage->parent->is($parentPage))->toBeTrue();
});

it('stores publication lifecycle fields for pages', function () {
    $user = User::factory()->create();

    $page = Page::query()->create([
        'template' => 'homepage',
        'status' => 'archived',
        'published_at' => now()->subDay(),
        'archived_at' => now(),
        'is_home' => true,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    expect($page->status)->toBe('archived')
        ->and($page->is_home)->toBeTrue()
        ->and($page->published_at)->not->toBeNull()
        ->and($page->archived_at)->not->toBeNull();
});

it('allows authorized users to view the pages index', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->create([
        'name' => 'pages.view',
        'guard_name' => 'web',
    ]));

    $this->actingAs($user)
        ->get(route('cms.pages.index'))
        ->assertSuccessful();
});

it('filters pages by workflow status in the cms index', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'pages.view',
        'guard_name' => 'web',
    ]));

    $draftPage = Page::query()->create([
        'template' => 'default',
        'status' => 'draft',
        'sort_order' => 1,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $draftPage->translations()->create([
        'locale' => 'en',
        'title' => 'Draft page',
        'slug' => 'draft-page',
    ]);

    $publishedPage = Page::query()->create([
        'template' => 'default',
        'status' => 'published',
        'published_at' => now(),
        'sort_order' => 2,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $publishedPage->translations()->create([
        'locale' => 'en',
        'title' => 'Published page',
        'slug' => 'published-page',
    ]);

    $this->actingAs($user)
        ->get(route('cms.pages.index', ['status' => 'draft']))
        ->assertSuccessful()
        ->assertSee('Draft page')
        ->assertDontSee('Published page');
});

it('stores a page and its translations through the cms route', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->create([
        'name' => 'pages.create',
        'guard_name' => 'web',
    ]));

    $response = $this
        ->withoutMiddleware()
        ->actingAs($user)
        ->post(route('cms.pages.store'), [
            'template' => 'default',
            'status' => 'draft',
            'sort_order' => 1,
            'is_home' => false,
            'translations' => [
                'en' => [
                    'title' => 'About Us',
                    'slug' => 'about-us',
                    'summary' => 'About summary',
                    'content_blocks' => json_encode([
                        ['id' => 'en-heading', 'type' => 'heading', 'content' => 'About heading', 'level' => 2],
                        ['id' => 'en-paragraph', 'type' => 'paragraph', 'content' => 'About content'],
                    ], JSON_THROW_ON_ERROR),
                ],
                'tj' => [
                    'title' => 'Дар бораи мо',
                    'slug' => 'dar-borai-mo',
                    'summary' => 'Маълумоти мухтасар',
                    'content_blocks' => json_encode([
                        ['id' => 'tj-paragraph', 'type' => 'paragraph', 'content' => 'Матн'],
                    ], JSON_THROW_ON_ERROR),
                ],
                'ru' => [
                    'title' => 'О нас',
                    'slug' => 'o-nas',
                    'summary' => 'Краткое описание',
                    'content_blocks' => json_encode([
                        ['id' => 'ru-list', 'type' => 'list', 'items' => ['Первый пункт', 'Второй пункт']],
                    ], JSON_THROW_ON_ERROR),
                ],
            ],
        ]);

    $response->assertRedirect();

    expect(Page::query()->count())->toBe(1)
        ->and(Page::query()->first()?->translations()->count())->toBe(3)
        ->and(Page::query()->first()?->translation('en')?->content)->toContain('<h2>About heading</h2>')
        ->and(Page::query()->first()?->translation('en')?->content)->toContain('<p>About content</p>')
        ->and(Page::query()->first()?->translation('ru')?->content)->toContain('<ul><li>Первый пункт</li><li>Второй пункт</li></ul>')
        ->and(Page::query()->first()?->translation('en')?->content_blocks)->toHaveCount(2);
});

it('does not allow contributors without publish permission to publish pages', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'pages.create',
        'guard_name' => 'web',
    ]));

    $this->withoutMiddleware()
        ->actingAs($user)
        ->post(route('cms.pages.store'), [
            'template' => 'default',
            'status' => 'published',
            'sort_order' => 1,
            'is_home' => false,
            'translations' => [
                'en' => ['title' => 'About Us', 'slug' => 'about-us'],
                'tj' => ['title' => 'Дар бораи мо', 'slug' => 'dar-borai-mo'],
                'ru' => ['title' => 'О нас', 'slug' => 'o-nas'],
            ],
        ])
        ->assertRedirect();

    expect(Page::query()->count())->toBe(0);
});

it('allows contributors to send pages to review through workflow actions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'pages.update',
        'guard_name' => 'web',
    ]));

    $page = Page::query()->create([
        'template' => 'default',
        'status' => 'draft',
        'sort_order' => 1,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $page->translations()->create([
        'locale' => 'en',
        'title' => 'Workflow page',
        'slug' => 'workflow-page',
    ]);

    $this->withoutMiddleware()
        ->actingAs($user)
        ->post(route('cms.pages.workflow', $page), [
            'status' => 'in_review',
        ])
        ->assertRedirect();

    expect($page->fresh()->status)->toBe('in_review');
});

it('allows editors to replace or remove the current page cover', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'pages.update',
        'guard_name' => 'web',
    ]));

    $page = Page::query()->create([
        'template' => 'default',
        'status' => 'draft',
        'sort_order' => 1,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $page->translations()->createMany([
        ['locale' => 'en', 'title' => 'About Us', 'slug' => 'about-us'],
        ['locale' => 'tj', 'title' => 'Дар бораи мо', 'slug' => 'dar-borai-mo'],
        ['locale' => 'ru', 'title' => 'О нас', 'slug' => 'o-nas'],
    ]);
    $page->addMedia(UploadedFile::fake()->image('old-cover.jpg'))
        ->toMediaCollection('cover');

    $token = 'page-update-token';

    $this->withSession(['_token' => $token])
        ->actingAs($user)
        ->put(route('cms.pages.update', $page), [
            '_token' => $token,
            'template' => 'default',
            'status' => 'draft',
            'sort_order' => 1,
            'is_home' => false,
            'cover' => UploadedFile::fake()->image('new-cover.jpg'),
            'translations' => [
                'en' => ['title' => 'About Us', 'slug' => 'about-us'],
                'tj' => ['title' => 'Дар бораи мо', 'slug' => 'dar-borai-mo'],
                'ru' => ['title' => 'О нас', 'slug' => 'o-nas'],
            ],
        ])
        ->assertRedirect();

    expect($page->fresh()->getMedia('cover'))->toHaveCount(1)
        ->and($page->fresh()->getFirstMedia('cover')?->name)->toBe('new-cover');

    $this->withSession(['_token' => $token])
        ->actingAs($user)
        ->put(route('cms.pages.update', $page), [
            '_token' => $token,
            'template' => 'default',
            'status' => 'draft',
            'sort_order' => 1,
            'is_home' => false,
            'remove_cover' => '1',
            'translations' => [
                'en' => ['title' => 'About Us', 'slug' => 'about-us'],
                'tj' => ['title' => 'Дар бораи мо', 'slug' => 'dar-borai-mo'],
                'ru' => ['title' => 'О нас', 'slug' => 'o-nas'],
            ],
        ])
        ->assertRedirect();

    expect($page->fresh()->getMedia('cover'))->toHaveCount(0);
});

<?php

use App\Models\Page;
use App\Models\User;
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
                    'content' => 'About content',
                ],
                'tj' => [
                    'title' => 'Дар бораи мо',
                    'slug' => 'dar-borai-mo',
                    'summary' => 'Маълумоти мухтасар',
                    'content' => 'Матн',
                ],
                'ru' => [
                    'title' => 'О нас',
                    'slug' => 'o-nas',
                    'summary' => 'Краткое описание',
                    'content' => 'Контент',
                ],
            ],
        ]);

    $response->assertRedirect();

    expect(Page::query()->count())->toBe(1)
        ->and(Page::query()->first()?->translations()->count())->toBe(3);
});

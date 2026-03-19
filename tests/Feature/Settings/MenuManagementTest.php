<?php

use App\Models\Menu;
use App\Models\User;
use Spatie\Permission\Models\Permission;

it('allows authorized users to view the menus index', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->create([
        'name' => 'navigation.view',
        'guard_name' => 'web',
    ]));

    $this->actingAs($user)
        ->get(route('cms.menus.index'))
        ->assertSuccessful();
});

it('stores menus with nested items through the cms route', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->create([
        'name' => 'navigation.create',
        'guard_name' => 'web',
    ]));

    $response = $this
        ->withoutMiddleware()
        ->actingAs($user)
        ->post(route('cms.menus.store'), [
            'name' => 'Main Navigation',
            'slug' => 'main-navigation',
            'location' => 'main',
            'items' => [
                [
                    'item_key' => 'about',
                    'label' => 'About',
                    'locale' => 'en',
                    'url' => '/about',
                    'route_name' => null,
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'item_key' => 'team',
                    'parent_item_key' => 'about',
                    'label' => 'Team',
                    'locale' => 'en',
                    'url' => '/about/team',
                    'route_name' => null,
                    'sort_order' => 2,
                    'is_active' => true,
                ],
            ],
        ]);

    $response->assertRedirect();

    $menu = Menu::query()->with('items.children')->first();

    expect($menu)->not->toBeNull()
        ->and($menu?->items)->toHaveCount(2)
        ->and($menu?->items->firstWhere('label', 'About')?->children)->toHaveCount(1);
});

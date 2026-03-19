<?php

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Setting;

it('stores and resolves settings by group and key', function () {
    Setting::query()->create([
        'group' => 'site',
        'key' => 'contact_email',
        'type' => 'string',
        'value' => 'info@example.test',
    ]);

    Setting::query()->create([
        'group' => 'social',
        'key' => 'links',
        'type' => 'json',
        'value' => [
            'telegram' => 'https://t.me/pic',
            'youtube' => 'https://youtube.com/@pic',
        ],
    ]);

    expect(Setting::for('site', 'contact_email'))->toBe('info@example.test')
        ->and(Setting::for('social', 'links'))->toBe([
            'telegram' => 'https://t.me/pic',
            'youtube' => 'https://youtube.com/@pic',
        ])
        ->and(Setting::for('site', 'missing_key', 'fallback'))->toBe('fallback');
});

it('supports menu items with hierarchy and locale metadata', function () {
    $menu = Menu::query()->create([
        'name' => 'Main Navigation',
        'slug' => 'main-navigation',
        'location' => 'main',
    ]);

    $parent = MenuItem::query()->create([
        'menu_id' => $menu->id,
        'label' => 'About',
        'locale' => 'en',
        'url' => '/en/about',
        'sort_order' => 1,
    ]);

    $child = MenuItem::query()->create([
        'menu_id' => $menu->id,
        'parent_id' => $parent->id,
        'label' => 'Team',
        'locale' => 'en',
        'url' => '/en/about/team',
        'sort_order' => 2,
    ]);

    expect($menu->items)->toHaveCount(2)
        ->and($parent->children)->toHaveCount(1)
        ->and($parent->children->first()->is($child))->toBeTrue()
        ->and($child->parent->is($parent))->toBeTrue()
        ->and($child->locale)->toBe('en');
});

<?php

use App\Models\Setting;
use App\Models\User;
use Spatie\Permission\Models\Permission;

it('allows authorized users to view the settings page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->create([
        'name' => 'settings.manage',
        'guard_name' => 'web',
    ]));

    $this->actingAs($user)
        ->get(route('cms.settings.edit'))
        ->assertSuccessful();
});

it('stores grouped site settings through the cms route', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->create([
        'name' => 'settings.manage',
        'guard_name' => 'web',
    ]));

    $response = $this
        ->withoutMiddleware()
        ->actingAs($user)
        ->put(route('cms.settings.update'), [
            'site_name' => 'Demo Portal',
            'site_tagline' => 'Transparency and services',
            'default_locale' => 'ru',
            'contact_email' => 'info@example.test',
            'contact_phone' => '+992900000000',
            'contact_address' => 'Dushanbe',
            'google_analytics_id' => 'G-TEST123',
            'telegram_url' => 'https://t.me/demo',
            'youtube_url' => 'https://youtube.com/@demo',
            'facebook_url' => 'https://facebook.com/demo',
            'linkedin_url' => 'https://linkedin.com/company/demo',
        ]);

    $response->assertRedirect(route('cms.settings.edit'));

    expect(Setting::for('site', 'name'))->toBe('Demo Portal')
        ->and(Setting::for('site', 'default_locale'))->toBe('ru')
        ->and(Setting::for('social', 'telegram_url'))->toBe('https://t.me/demo');
});

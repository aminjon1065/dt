<?php

use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('bootstraps the standard cms roles and permissions from the seeder', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    expect(Permission::query()->pluck('name')->all())
        ->toContain('pages.publish', 'news.publish', 'documents.publish', 'procurements.publish', 'settings.manage');

    $admin = Role::query()->where('name', 'admin')->firstOrFail();
    $editor = Role::query()->where('name', 'editor')->firstOrFail();
    $contributor = Role::query()->where('name', 'contributor')->firstOrFail();

    expect($admin->permissions)->toHaveCount(Permission::query()->count())
        ->and($editor->hasPermissionTo('pages.publish'))->toBeTrue()
        ->and($editor->hasPermissionTo('settings.manage'))->toBeFalse()
        ->and($editor->hasPermissionTo('navigation.update'))->toBeTrue()
        ->and($contributor->hasPermissionTo('pages.create'))->toBeTrue()
        ->and($contributor->hasPermissionTo('pages.publish'))->toBeFalse()
        ->and($contributor->hasPermissionTo('subscriptions.view'))->toBeFalse();
});

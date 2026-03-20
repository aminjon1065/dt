<?php

use App\Models\User;
use Database\Seeders\CmsUsersSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;

it('seeds cms users for each standard role', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(CmsUsersSeeder::class);

    $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
    $editor = User::query()->where('email', 'editor@example.com')->firstOrFail();
    $contributor = User::query()->where('email', 'contributor@example.com')->firstOrFail();

    expect($admin->hasRole('admin'))->toBeTrue()
        ->and($editor->hasRole('editor'))->toBeTrue()
        ->and($contributor->hasRole('contributor'))->toBeTrue()
        ->and($admin->email_verified_at)->not->toBeNull()
        ->and(User::query()->count())->toBe(3);
});

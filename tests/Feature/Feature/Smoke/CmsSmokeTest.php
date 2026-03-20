<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;

it('smoke tests the key cms routes for a content manager', function () {
    $this->withoutVite();

    $user = User::factory()->create();

    collect([
        'pages.view',
        'news.view',
        'documents.view',
        'procurements.view',
        'grm.view',
        'staff.view',
        'navigation.view',
        'subscriptions.view',
        'settings.manage',
    ])->each(function (string $permission) use ($user): void {
        $user->givePermissionTo(Permission::query()->firstOrCreate([
            'name' => $permission,
            'guard_name' => 'web',
        ]));
    });

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();

    $this->actingAs($user)
        ->get(route('cms.settings.edit'))
        ->assertSuccessful();

    $this->actingAs($user)
        ->get(route('cms.pages.index'))
        ->assertSuccessful();

    $this->actingAs($user)
        ->get(route('cms.news.index'))
        ->assertSuccessful();

    $this->actingAs($user)
        ->get(route('cms.documents.index'))
        ->assertSuccessful();

    $this->actingAs($user)
        ->get(route('cms.procurements.index'))
        ->assertSuccessful();

    $this->actingAs($user)
        ->get(route('cms.grm-submissions.index'))
        ->assertSuccessful();

    $this->actingAs($user)
        ->get(route('cms.staff-members.index'))
        ->assertSuccessful();

    $this->actingAs($user)
        ->get(route('cms.menus.index'))
        ->assertSuccessful();

    $this->actingAs($user)
        ->get(route('cms.subscriptions.index'))
        ->assertSuccessful();
});

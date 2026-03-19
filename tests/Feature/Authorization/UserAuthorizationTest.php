<?php

use App\Models\AuditLog;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('resolves permissions through assigned roles', function () {
    $user = User::factory()->create();
    $role = Role::query()->create([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);
    $permission = Permission::query()->create([
        'name' => 'pages.publish',
        'guard_name' => 'web',
    ]);
    $unassignedPermission = Permission::query()->create([
        'name' => 'documents.publish',
        'guard_name' => 'web',
    ]);

    $role->givePermissionTo($permission);
    $user->assignRole($role);

    expect($user->hasRole('admin'))->toBeTrue()
        ->and($user->hasAnyRole(['editor', 'admin']))->toBeTrue()
        ->and($user->hasPermissionTo('pages.publish'))->toBeTrue()
        ->and($user->hasPermissionTo($unassignedPermission))->toBeFalse();
});

it('stores audit logs with user and auditable relations', function () {
    $user = User::factory()->create();
    $role = Role::query()->create([
        'name' => 'editor',
        'guard_name' => 'web',
    ]);

    $auditLog = AuditLog::query()->create([
        'user_id' => $user->id,
        'event' => 'updated',
        'auditable_type' => $role->getMorphClass(),
        'auditable_id' => $role->id,
        'old_values' => ['name' => 'Writer'],
        'new_values' => ['name' => 'Editor'],
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
    ]);

    expect($auditLog->user->is($user))->toBeTrue()
        ->and($auditLog->auditable->is($role))->toBeTrue()
        ->and($auditLog->old_values)->toBe(['name' => 'Writer'])
        ->and($auditLog->new_values)->toBe(['name' => 'Editor']);
});

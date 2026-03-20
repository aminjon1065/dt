<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        collect($this->permissions())
            ->each(fn (string $permission): Permission => Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]));

        collect($this->roles())
            ->each(function (array $permissions, string $roleName): void {
                $role = Role::query()->firstOrCreate([
                    'name' => $roleName,
                    'guard_name' => 'web',
                ]);

                $role->syncPermissions($permissions);
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return array<int, string>
     */
    protected function permissions(): array
    {
        return [
            'pages.view',
            'pages.create',
            'pages.update',
            'pages.delete',
            'pages.publish',
            'news.view',
            'news.create',
            'news.update',
            'news.delete',
            'news.publish',
            'documents.view',
            'documents.create',
            'documents.update',
            'documents.delete',
            'documents.publish',
            'procurements.view',
            'procurements.create',
            'procurements.update',
            'procurements.delete',
            'procurements.publish',
            'grm.view',
            'grm.create',
            'grm.update',
            'grm.delete',
            'staff.view',
            'staff.create',
            'staff.update',
            'staff.delete',
            'staff.publish',
            'subscriptions.view',
            'subscriptions.create',
            'subscriptions.update',
            'subscriptions.delete',
            'navigation.view',
            'navigation.create',
            'navigation.update',
            'navigation.delete',
            'settings.manage',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function roles(): array
    {
        $allPermissions = $this->permissions();

        return [
            'admin' => $allPermissions,
            'editor' => [
                'pages.view',
                'pages.create',
                'pages.update',
                'pages.publish',
                'news.view',
                'news.create',
                'news.update',
                'news.publish',
                'documents.view',
                'documents.create',
                'documents.update',
                'documents.publish',
                'procurements.view',
                'procurements.create',
                'procurements.update',
                'procurements.publish',
                'grm.view',
                'grm.update',
                'staff.view',
                'staff.create',
                'staff.update',
                'staff.publish',
                'subscriptions.view',
                'navigation.view',
                'navigation.update',
            ],
            'contributor' => [
                'pages.view',
                'pages.create',
                'pages.update',
                'news.view',
                'news.create',
                'news.update',
                'documents.view',
                'documents.create',
                'documents.update',
                'procurements.view',
                'procurements.create',
                'procurements.update',
                'grm.view',
                'staff.view',
            ],
        ];
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CmsUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect($this->users())
            ->each(function (array $attributes): void {
                $role = $attributes['role'];

                $user = User::query()->updateOrCreate(
                    ['email' => $attributes['email']],
                    [
                        'name' => $attributes['name'],
                        'password' => Hash::make('password'),
                        'email_verified_at' => now(),
                    ],
                );

                $user->syncRoles([$role]);
            });
    }

    /**
     * @return array<int, array{name: string, email: string, role: string}>
     */
    protected function users(): array
    {
        return [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => 'admin',
            ],
            [
                'name' => 'Editor User',
                'email' => 'editor@example.com',
                'role' => 'editor',
            ],
            [
                'name' => 'Contributor User',
                'email' => 'contributor@example.com',
                'role' => 'contributor',
            ],
        ];
    }
}

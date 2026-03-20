<?php

use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;

it('supports hierarchical localized staff profiles', function () {
    $this->withoutVite();

    $user = User::factory()->create();

    $manager = StaffMember::query()->create([
        'email' => 'manager@example.com',
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $manager->translations()->createMany([
        [
            'locale' => 'en',
            'name' => 'Jane Manager',
            'slug' => 'jane-manager',
            'position' => 'Director',
        ],
        [
            'locale' => 'ru',
            'name' => 'Джейн Менеджер',
            'slug' => 'dzhein-menedzher',
            'position' => 'Директор',
        ],
    ]);

    $staffMember = StaffMember::query()->create([
        'parent_id' => $manager->id,
        'email' => 'staff@example.com',
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $staffMember->translations()->create([
        'locale' => 'en',
        'name' => 'John Officer',
        'slug' => 'john-officer',
        'position' => 'Officer',
    ]);

    expect($staffMember->parent?->is($manager))->toBeTrue()
        ->and($staffMember->translation('en')?->position)->toBe('Officer');
});

it('allows authorized users to view the staff index', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->create([
        'name' => 'staff.view',
        'guard_name' => 'web',
    ]));

    $this->actingAs($user)
        ->get(route('cms.staff-members.index'))
        ->assertSuccessful();
});

it('filters staff members in the cms index', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'staff.view',
        'guard_name' => 'web',
    ]));

    $manager = StaffMember::query()->create([
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $manager->translations()->create([
        'locale' => 'en',
        'name' => 'Jane Manager',
        'slug' => 'jane-manager',
        'position' => 'Director',
    ]);

    $staffMember = StaffMember::query()->create([
        'parent_id' => $manager->id,
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $staffMember->translations()->create([
        'locale' => 'en',
        'name' => 'John Officer',
        'slug' => 'john-officer',
        'position' => 'Officer',
    ]);

    StaffMember::query()->create([
        'status' => 'draft',
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ])->translations()->create([
        'locale' => 'en',
        'name' => 'Draft Profile',
        'slug' => 'draft-profile',
        'position' => 'Assistant',
    ]);

    $this->actingAs($user)
        ->get(route('cms.staff-members.index', [
            'search' => 'Officer',
            'status' => 'published',
            'parent_id' => $manager->id,
        ]))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('cms/staff-members/index')
            ->where('filters.search', 'Officer')
            ->where('filters.status', 'published')
            ->where('filters.parent_id', $manager->id)
            ->has('staffMembers', 1)
            ->where('staffMembers.0.translations.en.name', 'John Officer'));
});

it('stores a staff member and translations through the cms route', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->create([
        'name' => 'staff.create',
        'guard_name' => 'web',
    ]));

    $response = $this
        ->withoutMiddleware()
        ->actingAs($user)
        ->post(route('cms.staff-members.store'), [
            'email' => 'staff@example.com',
            'phone' => '+992900001122',
            'office_location' => 'Dushanbe',
            'show_email_publicly' => true,
            'show_phone_publicly' => true,
            'status' => 'published',
            'published_at' => now()->format('Y-m-d H:i:s'),
            'sort_order' => 1,
            'translations' => [
                'en' => [
                    'name' => 'John Officer',
                    'slug' => 'john-officer',
                    'position' => 'Officer',
                    'bio' => 'Biography',
                ],
                'tj' => [
                    'name' => 'Ҷон Афсар',
                    'slug' => 'jon-afsar',
                    'position' => 'Афсар',
                    'bio' => 'Тавсиф',
                ],
                'ru' => [
                    'name' => 'Джон Офицер',
                    'slug' => 'dzhon-oficer',
                    'position' => 'Офицер',
                    'bio' => 'Описание',
                ],
            ],
        ]);

    $response->assertRedirect();

    expect(StaffMember::query()->count())->toBe(1)
        ->and(StaffMember::query()->first()?->translations()->count())->toBe(3);
});

it('allows editors to replace or remove the current staff photo', function () {
    Storage::fake('public');
    $this->withoutVite();

    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'staff.update',
        'guard_name' => 'web',
    ]));

    $staffMember = StaffMember::query()->create([
        'status' => 'draft',
        'sort_order' => 1,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $staffMember->translations()->createMany([
        ['locale' => 'en', 'name' => 'John Officer', 'slug' => 'john-officer'],
        ['locale' => 'tj', 'name' => 'Ҷон Афсар', 'slug' => 'jon-afsar'],
        ['locale' => 'ru', 'name' => 'Джон Офицер', 'slug' => 'dzhon-oficer'],
    ]);
    $staffMember->addMedia(UploadedFile::fake()->image('old-photo.jpg'))
        ->toMediaCollection('profile_photo');

    $token = 'staff-photo-token';

    $this->withSession(['_token' => $token])
        ->actingAs($user)
        ->put(route('cms.staff-members.update', $staffMember), [
            '_token' => $token,
            'status' => 'draft',
            'sort_order' => 1,
            'show_email_publicly' => false,
            'show_phone_publicly' => false,
            'photo' => UploadedFile::fake()->image('new-photo.jpg'),
            'translations' => [
                'en' => ['name' => 'John Officer', 'slug' => 'john-officer'],
                'tj' => ['name' => 'Ҷон Афсар', 'slug' => 'jon-afsar'],
                'ru' => ['name' => 'Джон Офицер', 'slug' => 'dzhon-oficer'],
            ],
        ])
        ->assertRedirect();

    expect($staffMember->fresh()->getMedia('profile_photo'))->toHaveCount(1)
        ->and($staffMember->fresh()->getFirstMedia('profile_photo')?->name)->toBe('new-photo');

    $this->withSession(['_token' => $token])
        ->actingAs($user)
        ->put(route('cms.staff-members.update', $staffMember), [
            '_token' => $token,
            'status' => 'draft',
            'sort_order' => 1,
            'show_email_publicly' => false,
            'show_phone_publicly' => false,
            'remove_photo' => '1',
            'translations' => [
                'en' => ['name' => 'John Officer', 'slug' => 'john-officer'],
                'tj' => ['name' => 'Ҷон Афсар', 'slug' => 'jon-afsar'],
                'ru' => ['name' => 'Джон Офицер', 'slug' => 'dzhon-oficer'],
            ],
        ])
        ->assertRedirect();

    expect($staffMember->fresh()->getMedia('profile_photo'))->toHaveCount(0);
});

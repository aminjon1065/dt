<?php

use App\Models\StaffMember;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('renders the public staff directory with hierarchy', function () {
    $this->withoutVite();

    $user = User::factory()->create();

    $manager = StaffMember::query()->create([
        'email' => 'manager@example.com',
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $manager->translations()->create([
        'locale' => 'ru',
        'name' => 'Руководитель',
        'slug' => 'rukovoditel',
        'position' => 'Директор',
    ]);

    $staffMember = StaffMember::query()->create([
        'parent_id' => $manager->id,
        'email' => 'hidden@example.com',
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $staffMember->translations()->create([
        'locale' => 'ru',
        'name' => 'Специалист',
        'slug' => 'specialist',
        'position' => 'Координатор',
    ]);

    $this->get('/ru/staff')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/staff/index')
            ->where('staffMembers.0.name', 'Руководитель')
            ->where('staffMembers.0.children.0.name', 'Специалист'));
});

it('renders a public staff profile and hides email when not public', function () {
    $this->withoutVite();

    $user = User::factory()->create();

    $staffMember = StaffMember::query()->create([
        'email' => 'private@example.com',
        'phone' => '+992900001122',
        'show_email_publicly' => false,
        'show_phone_publicly' => true,
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
        'bio' => '<p>Profile biography</p>',
    ]);

    $this->get('/en/staff/john-officer')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/staff/show')
            ->where('staffMember.name', 'John Officer')
            ->where('staffMember.email', null)
            ->where('staffMember.phone', '+992900001122'));
});

<?php

use App\Models\Procurement;
use App\Models\User;
use Spatie\Permission\Models\Permission;

it('supports localized procurement notices', function () {
    $user = User::factory()->create();

    $procurement = Procurement::query()->create([
        'reference_number' => 'RFQ-2026-001',
        'procurement_type' => 'services',
        'status' => 'open',
        'published_at' => now(),
        'closing_at' => now()->addWeek(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $procurement->translations()->createMany([
        [
            'locale' => 'en',
            'title' => 'Consulting services procurement',
            'slug' => 'consulting-services-procurement',
            'summary' => 'Summary',
            'content' => 'Content',
        ],
        [
            'locale' => 'ru',
            'title' => 'Закупка консультационных услуг',
            'slug' => 'zakupka-konsultacionnyh-uslug',
            'summary' => 'Кратко',
            'content' => 'Контент',
        ],
    ]);

    expect($procurement->translation('en')?->title)->toBe(
        'Consulting services procurement'
    )->and($procurement->closing_at)->not->toBeNull();
});

it('allows authorized users to view the procurements index', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->create([
        'name' => 'procurements.view',
        'guard_name' => 'web',
    ]));

    $this->actingAs($user)
        ->get(route('cms.procurements.index'))
        ->assertSuccessful();
});

it('stores a procurement notice and translations through the cms route', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->create([
        'name' => 'procurements.create',
        'guard_name' => 'web',
    ]));

    $response = $this
        ->withoutMiddleware()
        ->actingAs($user)
        ->post(route('cms.procurements.store'), [
            'reference_number' => 'RFQ-2026-010',
            'procurement_type' => 'goods',
            'status' => 'planned',
            'translations' => [
                'en' => [
                    'title' => 'IT equipment supply',
                    'slug' => 'it-equipment-supply',
                    'summary' => 'Summary',
                    'content' => 'Content',
                ],
                'tj' => [
                    'title' => 'Таъминоти таҷҳизоти IT',
                    'slug' => 'taminoti-tajhizoti-it',
                    'summary' => 'Хулоса',
                    'content' => 'Матн',
                ],
                'ru' => [
                    'title' => 'Поставка IT-оборудования',
                    'slug' => 'postavka-it-oborudovaniya',
                    'summary' => 'Кратко',
                    'content' => 'Контент',
                ],
            ],
        ]);

    $response->assertRedirect();

    expect(Procurement::query()->count())->toBe(1)
        ->and(Procurement::query()->first()?->translations()->count())->toBe(3);
});

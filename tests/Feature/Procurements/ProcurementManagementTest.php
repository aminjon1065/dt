<?php

use App\Models\Procurement;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\ProcurementPublishedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
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

it('filters procurements by status in the cms index', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'procurements.view',
        'guard_name' => 'web',
    ]));

    $plannedProcurement = Procurement::query()->create([
        'reference_number' => 'RFQ-2026-100',
        'procurement_type' => 'goods',
        'status' => 'planned',
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $plannedProcurement->translations()->create([
        'locale' => 'en',
        'title' => 'Planned notice',
        'slug' => 'planned-notice',
    ]);

    $openProcurement = Procurement::query()->create([
        'reference_number' => 'RFQ-2026-101',
        'procurement_type' => 'services',
        'status' => 'open',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $openProcurement->translations()->create([
        'locale' => 'en',
        'title' => 'Open notice',
        'slug' => 'open-notice',
    ]);

    $this->actingAs($user)
        ->get(route('cms.procurements.index', ['status' => 'planned']))
        ->assertSuccessful()
        ->assertSee('Planned notice')
        ->assertDontSee('Open notice');
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
                    'content_blocks' => json_encode([
                        ['id' => 'en-heading', 'type' => 'heading', 'content' => 'IT equipment supply', 'level' => 2],
                        ['id' => 'en-paragraph', 'type' => 'paragraph', 'content' => 'Content'],
                    ], JSON_THROW_ON_ERROR),
                ],
                'tj' => [
                    'title' => 'Таъминоти таҷҳизоти IT',
                    'slug' => 'taminoti-tajhizoti-it',
                    'summary' => 'Хулоса',
                    'content_blocks' => json_encode([
                        ['id' => 'tj-quote', 'type' => 'quote', 'content' => 'Матн'],
                    ], JSON_THROW_ON_ERROR),
                ],
                'ru' => [
                    'title' => 'Поставка IT-оборудования',
                    'slug' => 'postavka-it-oborudovaniya',
                    'summary' => 'Кратко',
                    'content_blocks' => json_encode([
                        ['id' => 'ru-list', 'type' => 'list', 'items' => ['Контент 1', 'Контент 2']],
                    ], JSON_THROW_ON_ERROR),
                ],
            ],
        ]);

    $response->assertRedirect();

    expect(Procurement::query()->count())->toBe(1)
        ->and(Procurement::query()->first()?->translations()->count())->toBe(3)
        ->and(Procurement::query()->first()?->translation('en')?->content)->toContain('<h2>IT equipment supply</h2>')
        ->and(Procurement::query()->first()?->translation('tj')?->content)->toContain('<blockquote><p>Матн</p></blockquote>')
        ->and(Procurement::query()->first()?->translation('ru')?->content_blocks)->toHaveCount(1);
});

it('does not allow contributors without publish permission to open procurements', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'procurements.create',
        'guard_name' => 'web',
    ]));

    $this->withoutMiddleware()
        ->actingAs($user)
        ->post(route('cms.procurements.store'), [
            'reference_number' => 'RFQ-2026-011',
            'procurement_type' => 'goods',
            'status' => 'open',
            'translations' => [
                'en' => [
                    'title' => 'IT equipment supply',
                    'slug' => 'it-equipment-supply',
                ],
                'tj' => [
                    'title' => 'Таъминоти таҷҳизоти IT',
                    'slug' => 'taminoti-tajhizoti-it',
                ],
                'ru' => [
                    'title' => 'Поставка IT-оборудования',
                    'slug' => 'postavka-it-oborudovaniya',
                ],
            ],
        ])
        ->assertRedirect();

    expect(Procurement::query()->count())->toBe(0);
});

it('allows publishers to move procurements through workflow actions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::query()->firstOrCreate([
            'name' => 'procurements.update',
            'guard_name' => 'web',
        ]),
        Permission::query()->firstOrCreate([
            'name' => 'procurements.publish',
            'guard_name' => 'web',
        ]),
    ]);

    $procurement = Procurement::query()->create([
        'reference_number' => 'RFQ-2026-012',
        'procurement_type' => 'services',
        'status' => 'planned',
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $procurement->translations()->create([
        'locale' => 'en',
        'title' => 'Workflow procurement',
        'slug' => 'workflow-procurement',
    ]);

    $this->withoutMiddleware()
        ->actingAs($user)
        ->post(route('cms.procurements.workflow', $procurement), [
            'status' => 'open',
        ])
        ->assertRedirect();

    expect($procurement->fresh()->status)->toBe('open');
});

it('allows editors to remove selected procurement attachments without clearing the full collection', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'procurements.update',
        'guard_name' => 'web',
    ]));

    $procurement = Procurement::query()->create([
        'reference_number' => 'RFQ-2026-013',
        'procurement_type' => 'services',
        'status' => 'planned',
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $procurement->translations()->createMany([
        ['locale' => 'en', 'title' => 'IT equipment supply', 'slug' => 'it-equipment-supply'],
        ['locale' => 'tj', 'title' => 'Таъминоти таҷҳизоти IT', 'slug' => 'taminoti-tajhizoti-it'],
        ['locale' => 'ru', 'title' => 'Поставка IT-оборудования', 'slug' => 'postavka-it-oborudovaniya'],
    ]);

    $firstAttachment = $procurement
        ->addMedia(UploadedFile::fake()->create('terms.pdf', 64, 'application/pdf'))
        ->toMediaCollection('attachments');
    $secondAttachment = $procurement
        ->addMedia(UploadedFile::fake()->create('specs.pdf', 64, 'application/pdf'))
        ->toMediaCollection('attachments');

    $token = 'procurement-update-token';

    $this->withSession(['_token' => $token])
        ->actingAs($user)
        ->put(route('cms.procurements.update', $procurement), [
            '_token' => $token,
            'reference_number' => 'RFQ-2026-013',
            'procurement_type' => 'services',
            'status' => 'planned',
            'remove_attachment_ids' => [(string) $firstAttachment->id],
            'attachments' => [
                UploadedFile::fake()->create('addendum.pdf', 64, 'application/pdf'),
            ],
            'translations' => [
                'en' => ['title' => 'IT equipment supply', 'slug' => 'it-equipment-supply'],
                'tj' => ['title' => 'Таъминоти таҷҳизоти IT', 'slug' => 'taminoti-tajhizoti-it'],
                'ru' => ['title' => 'Поставка IT-оборудования', 'slug' => 'postavka-it-oborudovaniya'],
            ],
        ])
        ->assertRedirect();

    $mediaNames = $procurement->fresh()->getMedia('attachments')->pluck('name')->all();

    expect($mediaNames)->toContain('specs', 'addendum')
        ->and($mediaNames)->not->toContain('terms')
        ->and($procurement->fresh()->getMedia('attachments'))->toHaveCount(2)
        ->and($procurement->fresh()->getMedia('attachments')->pluck('id')->contains($secondAttachment->id))->toBeTrue();
});

it('sends email notifications to active subscriptions when a procurement notice is published', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::query()->firstOrCreate([
            'name' => 'procurements.create',
            'guard_name' => 'web',
        ]),
        Permission::query()->firstOrCreate([
            'name' => 'procurements.publish',
            'guard_name' => 'web',
        ]),
    ]);

    $activeSubscription = Subscription::query()->create([
        'email' => 'active@example.com',
        'locale' => 'en',
        'status' => 'active',
        'subscribed_at' => now(),
    ]);

    Subscription::query()->create([
        'email' => 'inactive@example.com',
        'locale' => 'en',
        'status' => 'unsubscribed',
        'subscribed_at' => now()->subDay(),
        'unsubscribed_at' => now(),
    ]);

    $response = $this
        ->withoutMiddleware()
        ->actingAs($user)
        ->post(route('cms.procurements.store'), [
            'reference_number' => 'RFQ-2026-020',
            'procurement_type' => 'services',
            'status' => 'open',
            'published_at' => now()->format('Y-m-d H:i:s'),
            'translations' => [
                'en' => [
                    'title' => 'Consulting services',
                    'slug' => 'consulting-services',
                    'summary' => 'Summary',
                    'content_blocks' => json_encode([
                        ['id' => 'en-paragraph', 'type' => 'paragraph', 'content' => 'Content'],
                    ], JSON_THROW_ON_ERROR),
                ],
                'tj' => [
                    'title' => 'Хизматрасониҳои машваратӣ',
                    'slug' => 'hizmatrasonihoi-mashvarati',
                    'summary' => 'Хулоса',
                    'content_blocks' => json_encode([
                        ['id' => 'tj-paragraph', 'type' => 'paragraph', 'content' => 'Матн'],
                    ], JSON_THROW_ON_ERROR),
                ],
                'ru' => [
                    'title' => 'Консультационные услуги',
                    'slug' => 'konsultacionnye-uslugi',
                    'summary' => 'Кратко',
                    'content_blocks' => json_encode([
                        ['id' => 'ru-paragraph', 'type' => 'paragraph', 'content' => 'Контент'],
                    ], JSON_THROW_ON_ERROR),
                ],
            ],
        ]);

    $response->assertRedirect();

    Notification::assertSentOnDemand(
        ProcurementPublishedNotification::class,
        function (ProcurementPublishedNotification $notification, array $channels, object $notifiable): bool {
            return in_array('mail', $channels, true)
                && $notifiable->routes['mail'] === 'active@example.com';
        },
    );

    $procurement = Procurement::query()->first();

    expect($procurement?->subscription_notified_at)->not->toBeNull()
        ->and($activeSubscription->fresh()?->last_notified_at)->not->toBeNull();
});

<?php

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentTag;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\DocumentPublishedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

it('supports localized documents with categories and tags', function () {
    $user = User::factory()->create();
    $category = DocumentCategory::query()->create([
        'slug' => 'policies',
        'is_active' => true,
    ]);
    $category->translations()->create([
        'locale' => 'en',
        'name' => 'Policies',
    ]);

    $tag = DocumentTag::query()->create([
        'slug' => 'public',
    ]);
    $tag->translations()->create([
        'locale' => 'en',
        'name' => 'Public',
    ]);

    $document = Document::query()->create([
        'document_category_id' => $category->id,
        'status' => 'published',
        'file_type' => 'pdf',
        'document_date' => now()->toDateString(),
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $document->translations()->createMany([
        [
            'locale' => 'en',
            'title' => 'Project policy',
            'slug' => 'project-policy',
            'summary' => 'Summary',
        ],
        [
            'locale' => 'ru',
            'title' => 'Политика проекта',
            'slug' => 'politika-proekta',
            'summary' => 'Кратко',
        ],
    ]);
    $document->tags()->attach($tag);

    expect($document->translation('en')?->title)->toBe('Project policy')
        ->and($document->category->translation('en')?->name)->toBe('Policies')
        ->and($document->tags->first()?->translation('en')?->name)->toBe('Public');
});

it('allows authorized users to view the documents index', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->create([
        'name' => 'documents.view',
        'guard_name' => 'web',
    ]));

    $this->actingAs($user)
        ->get(route('cms.documents.index'))
        ->assertSuccessful();
});

it('filters documents by workflow status in the cms index', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'documents.view',
        'guard_name' => 'web',
    ]));

    $category = DocumentCategory::query()->create([
        'slug' => 'reports',
        'is_active' => true,
    ]);
    $category->translations()->create([
        'locale' => 'en',
        'name' => 'Reports',
    ]);

    $draftDocument = Document::query()->create([
        'document_category_id' => $category->id,
        'status' => 'draft',
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $draftDocument->translations()->create([
        'locale' => 'en',
        'title' => 'Draft document',
        'slug' => 'draft-document',
    ]);

    $publishedDocument = Document::query()->create([
        'document_category_id' => $category->id,
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $publishedDocument->translations()->create([
        'locale' => 'en',
        'title' => 'Published document',
        'slug' => 'published-document',
    ]);

    $this->actingAs($user)
        ->get(route('cms.documents.index', ['status' => 'draft']))
        ->assertSuccessful()
        ->assertSee('Draft document')
        ->assertDontSee('Published document');
});

it('stores a document and translations through the cms route', function () {
    UploadedFile::fake()->create('policy.pdf', 64, 'application/pdf');

    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->create([
        'name' => 'documents.create',
        'guard_name' => 'web',
    ]));

    $category = DocumentCategory::query()->create([
        'slug' => 'reports',
        'is_active' => true,
    ]);
    $category->translations()->create([
        'locale' => 'en',
        'name' => 'Reports',
    ]);

    $tag = DocumentTag::query()->create([
        'slug' => 'featured',
    ]);
    $tag->translations()->create([
        'locale' => 'en',
        'name' => 'Featured',
    ]);

    $response = $this
        ->withoutMiddleware()
        ->actingAs($user)
        ->post(route('cms.documents.store'), [
            'document_category_id' => $category->id,
            'status' => 'draft',
            'file_type' => 'pdf',
            'tag_ids' => [$tag->id],
            'file' => UploadedFile::fake()->create('policy.pdf', 64, 'application/pdf'),
            'translations' => [
                'en' => [
                    'title' => 'Annual report',
                    'slug' => 'annual-report',
                    'summary' => 'Summary',
                    'content_blocks' => json_encode([
                        ['id' => 'en-heading', 'type' => 'heading', 'content' => 'Annual report', 'level' => 2],
                        ['id' => 'en-paragraph', 'type' => 'paragraph', 'content' => 'Document content'],
                    ], JSON_THROW_ON_ERROR),
                    'seo_title' => 'Annual report SEO',
                    'seo_description' => 'Annual report SEO description',
                ],
                'tj' => [
                    'title' => 'Ҳисоботи солона',
                    'slug' => 'hisoboti-solona',
                    'summary' => 'Хулоса',
                    'content_blocks' => json_encode([
                        ['id' => 'tj-quote', 'type' => 'quote', 'content' => 'Тавсифи ҳуҷҷат'],
                    ], JSON_THROW_ON_ERROR),
                ],
                'ru' => [
                    'title' => 'Годовой отчет',
                    'slug' => 'godovoj-otchet',
                    'summary' => 'Кратко',
                    'content_blocks' => json_encode([
                        ['id' => 'ru-list', 'type' => 'list', 'items' => ['Пункт 1', 'Пункт 2']],
                    ], JSON_THROW_ON_ERROR),
                ],
            ],
        ]);

    $response->assertRedirect();

    expect(Document::query()->count())->toBe(1)
        ->and(Document::query()->first()?->translations()->count())->toBe(3)
        ->and(Document::query()->first()?->tags()->count())->toBe(1)
        ->and(Document::query()->first()?->translation('en')?->content)->toContain('<h2>Annual report</h2>')
        ->and(Document::query()->first()?->translation('tj')?->content)->toContain('<blockquote><p>Тавсифи ҳуҷҷат</p></blockquote>')
        ->and(Document::query()->first()?->translation('en')?->seo_title)->toBe('Annual report SEO');
});

it('does not allow contributors without publish permission to publish documents', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'documents.create',
        'guard_name' => 'web',
    ]));

    $category = DocumentCategory::query()->create([
        'slug' => 'reports',
        'is_active' => true,
    ]);
    $category->translations()->create([
        'locale' => 'en',
        'name' => 'Reports',
    ]);

    $this->withoutMiddleware()
        ->actingAs($user)
        ->post(route('cms.documents.store'), [
            'document_category_id' => $category->id,
            'status' => 'published',
            'file' => UploadedFile::fake()->create('policy.pdf', 64, 'application/pdf'),
            'translations' => [
                'en' => ['title' => 'Annual report', 'slug' => 'annual-report'],
                'tj' => ['title' => 'Ҳисоботи солона', 'slug' => 'hisoboti-solona'],
                'ru' => ['title' => 'Годовой отчет', 'slug' => 'godovoj-otchet'],
            ],
        ])
        ->assertRedirect();

    expect(Document::query()->count())->toBe(0);
});

it('allows contributors to send documents to review through workflow actions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'documents.update',
        'guard_name' => 'web',
    ]));

    $category = DocumentCategory::query()->create([
        'slug' => 'reports',
        'is_active' => true,
    ]);
    $category->translations()->create([
        'locale' => 'en',
        'name' => 'Reports',
    ]);

    $document = Document::query()->create([
        'document_category_id' => $category->id,
        'status' => 'draft',
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $document->translations()->create([
        'locale' => 'en',
        'title' => 'Workflow document',
        'slug' => 'workflow-document',
    ]);

    $this->withoutMiddleware()
        ->actingAs($user)
        ->post(route('cms.documents.workflow', $document), [
            'status' => 'in_review',
        ])
        ->assertRedirect();

    expect($document->fresh()->status)->toBe('in_review');
});

it('allows editors to replace or remove the current document file', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->firstOrCreate([
        'name' => 'documents.update',
        'guard_name' => 'web',
    ]));

    $category = DocumentCategory::query()->create([
        'slug' => 'reports',
        'is_active' => true,
    ]);
    $category->translations()->create([
        'locale' => 'en',
        'name' => 'Reports',
    ]);

    $document = Document::query()->create([
        'document_category_id' => $category->id,
        'status' => 'draft',
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $document->translations()->createMany([
        ['locale' => 'en', 'title' => 'Annual report', 'slug' => 'annual-report'],
        ['locale' => 'tj', 'title' => 'Ҳисоботи солона', 'slug' => 'hisoboti-solona'],
        ['locale' => 'ru', 'title' => 'Годовой отчет', 'slug' => 'godovoj-otchet'],
    ]);
    $document->addMedia(UploadedFile::fake()->create('old-policy.pdf', 64, 'application/pdf'))
        ->toMediaCollection('documents');

    $token = 'document-update-token';

    $this->withSession(['_token' => $token])
        ->actingAs($user)
        ->put(route('cms.documents.update', $document), [
            '_token' => $token,
            'document_category_id' => $category->id,
            'status' => 'draft',
            'file' => UploadedFile::fake()->create('new-policy.pdf', 64, 'application/pdf'),
            'translations' => [
                'en' => ['title' => 'Annual report', 'slug' => 'annual-report'],
                'tj' => ['title' => 'Ҳисоботи солона', 'slug' => 'hisoboti-solona'],
                'ru' => ['title' => 'Годовой отчет', 'slug' => 'godovoj-otchet'],
            ],
        ])
        ->assertRedirect();

    expect($document->fresh()->getMedia('documents'))->toHaveCount(1)
        ->and($document->fresh()->getFirstMedia('documents')?->name)->toBe('new-policy');

    $this->withSession(['_token' => $token])
        ->actingAs($user)
        ->put(route('cms.documents.update', $document), [
            '_token' => $token,
            'document_category_id' => $category->id,
            'status' => 'draft',
            'remove_file' => '1',
            'translations' => [
                'en' => ['title' => 'Annual report', 'slug' => 'annual-report'],
                'tj' => ['title' => 'Ҳисоботи солона', 'slug' => 'hisoboti-solona'],
                'ru' => ['title' => 'Годовой отчет', 'slug' => 'godovoj-otchet'],
            ],
        ])
        ->assertRedirect();

    expect($document->fresh()->getMedia('documents'))->toHaveCount(0);
});

it('sends email notifications to active subscriptions when a document is published', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::query()->firstOrCreate([
            'name' => 'documents.create',
            'guard_name' => 'web',
        ]),
        Permission::query()->firstOrCreate([
            'name' => 'documents.publish',
            'guard_name' => 'web',
        ]),
    ]);

    $category = DocumentCategory::query()->create([
        'slug' => 'reports',
        'is_active' => true,
    ]);
    $category->translations()->create([
        'locale' => 'en',
        'name' => 'Reports',
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
        ->post(route('cms.documents.store'), [
            'document_category_id' => $category->id,
            'status' => 'published',
            'file_type' => 'pdf',
            'document_date' => now()->toDateString(),
            'published_at' => now()->format('Y-m-d H:i:s'),
            'file' => UploadedFile::fake()->create('report.pdf', 64, 'application/pdf'),
            'translations' => [
                'en' => [
                    'title' => 'Annual report',
                    'slug' => 'annual-report',
                    'summary' => 'Summary',
                    'content_blocks' => json_encode([
                        ['id' => 'en-paragraph', 'type' => 'paragraph', 'content' => 'Document content'],
                    ], JSON_THROW_ON_ERROR),
                ],
                'tj' => [
                    'title' => 'Ҳисоботи солона',
                    'slug' => 'hisoboti-solona',
                    'summary' => 'Хулоса',
                    'content_blocks' => json_encode([
                        ['id' => 'tj-paragraph', 'type' => 'paragraph', 'content' => 'Матн'],
                    ], JSON_THROW_ON_ERROR),
                ],
                'ru' => [
                    'title' => 'Годовой отчет',
                    'slug' => 'godovoj-otchet',
                    'summary' => 'Кратко',
                    'content_blocks' => json_encode([
                        ['id' => 'ru-paragraph', 'type' => 'paragraph', 'content' => 'Контент'],
                    ], JSON_THROW_ON_ERROR),
                ],
            ],
        ]);

    $response->assertRedirect();

    Notification::assertSentOnDemand(
        DocumentPublishedNotification::class,
        function (DocumentPublishedNotification $notification, array $channels, object $notifiable): bool {
            return in_array('mail', $channels, true)
                && $notifiable->routes['mail'] === 'active@example.com';
        },
    );

    $document = Document::query()->first();

    expect($document?->subscription_notified_at)->not->toBeNull()
        ->and($activeSubscription->fresh()?->last_notified_at)->not->toBeNull();
});

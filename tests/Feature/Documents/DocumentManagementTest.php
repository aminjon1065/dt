<?php

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentTag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
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
                ],
                'tj' => [
                    'title' => 'Ҳисоботи солона',
                    'slug' => 'hisoboti-solona',
                    'summary' => 'Хулоса',
                ],
                'ru' => [
                    'title' => 'Годовой отчет',
                    'slug' => 'godovoj-otchet',
                    'summary' => 'Кратко',
                ],
            ],
        ]);

    $response->assertRedirect();

    expect(Document::query()->count())->toBe(1)
        ->and(Document::query()->first()?->translations()->count())->toBe(3)
        ->and(Document::query()->first()?->tags()->count())->toBe(1);
});

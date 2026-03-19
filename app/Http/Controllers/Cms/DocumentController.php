<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentTag;
use App\Models\DocumentTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Document::class);

        $documents = Document::query()
            ->with(['category.translations', 'tags.translations', 'translations'])
            ->orderByDesc('published_at')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn(Document $document): array => [
                'id' => $document->id,
                'status' => $document->status,
                'file_type' => $document->file_type,
                'document_date' => $document->document_date?->toDateString(),
                'published_at' => $document->published_at?->toIso8601String(),
                'category' => $document->category->translation('en')?->name ?? $document->category->slug,
                'tags' => $document->tags->map(
                    fn(DocumentTag $tag): string => $tag->translation('en')?->name ?? $tag->slug
                )->values()->all(),
                'translations' => $document->translations->mapWithKeys(
                    fn(DocumentTranslation $translation) => [$translation->locale => [
                        'title' => $translation->title,
                        'slug' => $translation->slug,
                    ]]
                ),
                'file_url' => $document->getFirstMediaUrl('documents') ?: null,
            ]);

        return Inertia::render('cms/documents/index', [
            'documents' => $documents,
            'status' => session('status'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Document::class);

        return Inertia::render('cms/documents/create', [
            'categories' => $this->categories(),
            'tags' => $this->tags(),
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $document = DB::transaction(function () use ($request): Document {
            $document = Document::query()->create([
                'document_category_id' => $request->validated('document_category_id'),
                'status' => $request->validated('status'),
                'file_type' => $request->validated('file_type'),
                'document_date' => $request->validated('document_date'),
                'published_at' => $request->validated('published_at'),
                'archived_at' => $request->validated('archived_at'),
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $this->syncTranslations($document, $request->validated('translations'));
            $document->tags()->sync($request->validated('tag_ids', []));
            $this->syncFile($document, $request);
            $this->recordAudit($request, 'created', $document, null, $document->fresh()->toArray());

            return $document;
        });

        return to_route('cms.documents.edit', $document)->with('status', 'document-created');
    }

    public function edit(Document $document): Response
    {
        $this->authorize('update', $document);

        $document->load(['translations', 'tags', 'category']);

        return Inertia::render('cms/documents/edit', [
            'document' => [
                'id' => $document->id,
                'document_category_id' => $document->document_category_id,
                'status' => $document->status,
                'file_type' => $document->file_type,
                'document_date' => $document->document_date?->toDateString(),
                'published_at' => $document->published_at?->format('Y-m-d\\TH:i'),
                'archived_at' => $document->archived_at?->format('Y-m-d\\TH:i'),
                'tag_ids' => $document->tags->pluck('id')->all(),
                'file_url' => $document->getFirstMediaUrl('documents') ?: null,
                'translations' => $document->translations->mapWithKeys(
                    fn(DocumentTranslation $translation) => [$translation->locale => [
                        'title' => $translation->title,
                        'slug' => $translation->slug,
                        'summary' => $translation->summary,
                    ]]
                ),
            ],
            'categories' => $this->categories(),
            'tags' => $this->tags(),
            'status' => session('status'),
        ]);
    }

    public function update(
        UpdateDocumentRequest $request,
        Document              $document,
    ): RedirectResponse
    {
        DB::transaction(function () use ($request, $document): void {
            $oldValues = $document->fresh()->toArray();

            $document->update([
                'document_category_id' => $request->validated('document_category_id'),
                'status' => $request->validated('status'),
                'file_type' => $request->validated('file_type'),
                'document_date' => $request->validated('document_date'),
                'published_at' => $request->validated('published_at'),
                'archived_at' => $request->validated('archived_at'),
                'updated_by' => $request->user()->id,
            ]);

            $this->syncTranslations($document, $request->validated('translations'));
            $document->tags()->sync($request->validated('tag_ids', []));
            $this->syncFile($document, $request);
            $this->recordAudit($request, 'updated', $document, $oldValues, $document->fresh()->toArray());
        });

        return to_route('cms.documents.edit', $document)->with('status', 'document-updated');
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $oldValues = $document->toArray();

        $document->delete();

        $this->recordAudit($request, 'deleted', $document, $oldValues, null);

        return to_route('cms.documents.index')->with('status', 'document-deleted');
    }

    /**
     * @param array<string, array<string, mixed>> $translations
     */
    protected function syncTranslations(Document $document, array $translations): void
    {
        foreach ($translations as $locale => $translation) {
            $document->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $translation['title'],
                    'slug' => $translation['slug'],
                    'summary' => $translation['summary'] ?? null,
                ],
            );
        }
    }

    protected function syncFile(Document $document, Request $request): void
    {
        if (!$request->hasFile('file')) {
            return;
        }

        $document->clearMediaCollection('documents');
        $document->addMediaFromRequest('file')->toMediaCollection('documents');
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    protected function categories(): array
    {
        return DocumentCategory::query()
            ->with('translations')
            ->where('is_active', true)
            ->orderBy('slug')
            ->get()
            ->map(fn(DocumentCategory $category): array => [
                'id' => $category->id,
                'name' => $category->translation('en')?->name ?? $category->slug,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    protected function tags(): array
    {
        return DocumentTag::query()
            ->with('translations')
            ->orderBy('slug')
            ->get()
            ->map(fn(DocumentTag $tag): array => [
                'id' => $tag->id,
                'name' => $tag->translation('en')?->name ?? $tag->slug,
            ])
            ->all();
    }

    protected function recordAudit(
        Request  $request,
        string   $event,
        Document $document,
        ?array   $oldValues,
        ?array   $newValues,
    ): void
    {
        AuditLog::query()->create([
            'user_id' => $request->user()?->id,
            'event' => $event,
            'auditable_type' => $document->getMorphClass(),
            'auditable_id' => $document->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

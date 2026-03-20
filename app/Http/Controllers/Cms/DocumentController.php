<?php

namespace App\Http\Controllers\Cms;

use App\Enums\ContentStatus;
use App\Http\Controllers\Concerns\HasCmsWorkflow;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterDocumentsRequest;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Http\Requests\UpdateDocumentWorkflowRequest;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentTag;
use App\Models\DocumentTranslation;
use App\Models\User;
use App\Support\ContentBlocks\ContentBlockRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    use HasCmsWorkflow;

    public function index(FilterDocumentsRequest $request): Response
    {
        $this->authorize('viewAny', Document::class);

        $filters = $request->validated();

        $documents = Document::query()
            ->with(['category.translations', 'tags.translations', 'translations'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('published_at')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Document $document): array => [
                'id' => $document->id,
                'status' => $document->status,
                'file_type' => $document->file_type,
                'document_date' => $document->document_date?->toDateString(),
                'published_at' => $document->published_at?->toIso8601String(),
                'category' => $document->category->translation('en')?->name ?? $document->category->slug,
                'tags' => $document->tags->map(
                    fn (DocumentTag $tag): string => $tag->translation('en')?->name ?? $tag->slug
                )->values()->all(),
                'translations' => $document->translations->mapWithKeys(
                    fn (DocumentTranslation $translation) => [$translation->locale => [
                        'title' => $translation->title,
                        'slug' => $translation->slug,
                    ]]
                ),
                'file_url' => $document->getFirstMediaUrl('documents') ?: null,
            ]);

        return Inertia::render('cms/documents/index', [
            'documents' => $documents,
            'filters' => [
                'status' => $filters['status'] ?? null,
            ],
            'status' => session('status'),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Document::class);

        return Inertia::render('cms/documents/create', [
            'categories' => $this->categories(),
            'tags' => $this->tags(),
            'availableStatuses' => $this->availableStatuses($request->user()),
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $this->ensurePublishableStatus($request, Document::class);

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
        $currentFile = $document->getFirstMedia('documents');

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
                'current_file' => $currentFile ? [
                    'id' => $currentFile->id,
                    'name' => $currentFile->name,
                    'url' => $currentFile->getUrl(),
                ] : null,
                'translations' => $document->translations->mapWithKeys(
                    fn (DocumentTranslation $translation) => [$translation->locale => [
                        'title' => $translation->title,
                        'slug' => $translation->slug,
                        'summary' => $translation->summary,
                        'content' => $translation->content,
                        'content_blocks' => $translation->content_blocks,
                        'seo_title' => $translation->seo_title,
                        'seo_description' => $translation->seo_description,
                    ]]
                ),
            ],
            'categories' => $this->categories(),
            'tags' => $this->tags(),
            'availableStatuses' => $this->availableStatuses(request()->user()),
            'canPublish' => request()->user()?->can('publish', Document::class) ?? false,
            'status' => session('status'),
        ]);
    }

    public function update(
        UpdateDocumentRequest $request,
        Document $document,
    ): RedirectResponse {
        $this->ensurePublishableStatus($request, Document::class);

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

    public function workflow(UpdateDocumentWorkflowRequest $request, Document $document): RedirectResponse
    {
        $document = $document->exists ? $document : Document::query()->findOrFail($request->route('document'));
        $oldValues = $document->toArray();
        $status = $request->string('status')->toString();

        Document::query()->whereKey($document->getKey())->update([
            'status' => $status,
            'published_at' => $status === ContentStatus::Published->value
                ? ($document->published_at ?? now())
                : $document->published_at,
            'archived_at' => $status === ContentStatus::Archived->value ? now() : null,
            'updated_by' => $request->user()->id,
        ]);

        $this->recordAudit($request, 'workflow-updated', $document, $oldValues, $document->refresh()->toArray());

        return back()->with('status', 'document-workflow-updated');
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
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
                    'content' => ContentBlockRenderer::toHtml($translation['content_blocks'] ?? [])
                        ?? ($translation['content'] ?? null),
                    'content_blocks' => ContentBlockRenderer::normalize($translation['content_blocks'] ?? []),
                    'seo_title' => $translation['seo_title'] ?? null,
                    'seo_description' => $translation['seo_description'] ?? null,
                ],
            );
        }
    }

    protected function syncFile(Document $document, Request $request): void
    {
        if ($request->boolean('remove_file')) {
            $document->clearMediaCollection('documents');
        } elseif ($request->hasFile('file')) {
            $document->clearMediaCollection('documents');
            $document->addMediaFromRequest('file')->toMediaCollection('documents');
        }
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
            ->map(fn (DocumentCategory $category): array => [
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
            ->map(fn (DocumentTag $tag): array => [
                'id' => $tag->id,
                'name' => $tag->translation('en')?->name ?? $tag->slug,
            ])
            ->all();
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    protected function availableStatuses(?User $user): array
    {
        $statuses = $user?->can('publish', Document::class)
            ? ContentStatus::values()
            : ContentStatus::editableValues();

        return collect($statuses)
            ->map(fn (string $status): array => [
                'value' => $status,
                'label' => str($status)->replace('_', ' ')->title()->value(),
            ])
            ->all();
    }
}

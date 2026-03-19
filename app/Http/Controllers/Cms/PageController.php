<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Models\AuditLog;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Page::class);

        $pages = Page::query()
            ->with(['parent:id', 'translations'])
            ->orderBy('sort_order')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Page $page): array => [
                'id' => $page->id,
                'parent_id' => $page->parent_id,
                'parent_title' => $page->parent?->translation('en')?->title,
                'template' => $page->template,
                'status' => $page->status,
                'published_at' => $page->published_at?->toIso8601String(),
                'archived_at' => $page->archived_at?->toIso8601String(),
                'sort_order' => $page->sort_order,
                'is_home' => $page->is_home,
                'translations' => $page->translations->mapWithKeys(
                    fn ($translation) => [$translation->locale => [
                        'title' => $translation->title,
                        'slug' => $translation->slug,
                    ]]
                ),
            ]);

        return Inertia::render('cms/pages/index', [
            'pages' => $pages,
            'status' => session('status'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create', Page::class);

        return Inertia::render('cms/pages/create', [
            'parentPages' => $this->parentPages(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePageRequest $request): RedirectResponse
    {
        $page = DB::transaction(function () use ($request): Page {
            $page = Page::query()->create([
                'parent_id' => $request->validated('parent_id'),
                'template' => $request->validated('template'),
                'status' => $request->validated('status'),
                'published_at' => $request->validated('published_at'),
                'archived_at' => $request->validated('archived_at'),
                'sort_order' => $request->validated('sort_order'),
                'is_home' => $request->boolean('is_home'),
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $this->syncTranslations($page, $request->validated('translations'));
            $this->syncCover($page, $request);
            $this->recordAudit($request, 'created', $page, null, $page->fresh()->toArray());

            return $page;
        });

        return to_route('cms.pages.edit', $page)->with('status', 'page-created');
    }

    /**
     * Display the specified resource.
     */
    public function edit(Page $page): Response
    {
        $this->authorize('update', $page);

        $page->load('translations');

        return Inertia::render('cms/pages/edit', [
            'page' => [
                'id' => $page->id,
                'parent_id' => $page->parent_id,
                'template' => $page->template,
                'status' => $page->status,
                'published_at' => $page->published_at?->format('Y-m-d\\TH:i'),
                'archived_at' => $page->archived_at?->format('Y-m-d\\TH:i'),
                'sort_order' => $page->sort_order,
                'is_home' => $page->is_home,
                'translations' => $page->translations->mapWithKeys(
                    fn ($translation) => [$translation->locale => [
                        'title' => $translation->title,
                        'slug' => $translation->slug,
                        'summary' => $translation->summary,
                        'content' => $translation->content,
                        'seo_title' => $translation->seo_title,
                        'seo_description' => $translation->seo_description,
                    ]]
                ),
                'cover_url' => $page->getFirstMediaUrl('cover') ?: null,
            ],
            'parentPages' => $this->parentPages($page),
            'status' => session('status'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        DB::transaction(function () use ($request, $page): void {
            $oldValues = $page->fresh()->toArray();

            $page->update([
                'parent_id' => $request->validated('parent_id'),
                'template' => $request->validated('template'),
                'status' => $request->validated('status'),
                'published_at' => $request->validated('published_at'),
                'archived_at' => $request->validated('archived_at'),
                'sort_order' => $request->validated('sort_order'),
                'is_home' => $request->boolean('is_home'),
                'updated_by' => $request->user()->id,
            ]);

            $this->syncTranslations($page, $request->validated('translations'));
            $this->syncCover($page, $request);
            $this->recordAudit($request, 'updated', $page, $oldValues, $page->fresh()->toArray());
        });

        return to_route('cms.pages.edit', $page)->with('status', 'page-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Page $page): RedirectResponse
    {
        $this->authorize('delete', $page);

        $oldValues = $page->toArray();

        $page->delete();

        $this->recordAudit($request, 'deleted', $page, $oldValues, null);

        return to_route('cms.pages.index')->with('status', 'page-deleted');
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    protected function syncTranslations(Page $page, array $translations): void
    {
        foreach ($translations as $locale => $translation) {
            $page->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $translation['title'],
                    'slug' => $translation['slug'],
                    'summary' => $translation['summary'] ?? null,
                    'content' => $translation['content'] ?? null,
                    'seo_title' => $translation['seo_title'] ?? null,
                    'seo_description' => $translation['seo_description'] ?? null,
                ],
            );
        }
    }

    protected function syncCover(Page $page, Request $request): void
    {
        if ($request->hasFile('cover')) {
            $page->clearMediaCollection('cover');
            $page->addMediaFromRequest('cover')->toMediaCollection('cover');
        }
    }

    /**
     * @return array<int, array{id:int,title:string}>
     */
    protected function parentPages(?Page $excludingPage = null): array
    {
        return Page::query()
            ->with('translations')
            ->when($excludingPage, fn ($query) => $query->whereKeyNot($excludingPage->id))
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Page $page): array => [
                'id' => $page->id,
                'title' => $page->translation('en')?->title ?? "Page #{$page->id}",
            ])
            ->all();
    }

    protected function recordAudit(
        Request $request,
        string $event,
        Page $page,
        ?array $oldValues,
        ?array $newValues,
    ): void {
        AuditLog::query()->create([
            'user_id' => $request->user()?->id,
            'event' => $event,
            'auditable_type' => $page->getMorphClass(),
            'auditable_id' => $page->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

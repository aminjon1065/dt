<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterNewsRequest;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Http\Requests\UpdateNewsWorkflowRequest;
use App\Models\AuditLog;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTranslation;
use App\Models\User;
use App\Support\ContentBlocks\ContentBlockRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(FilterNewsRequest $request): Response
    {
        $this->authorize('viewAny', News::class);

        $filters = $request->validated();

        $newsItems = News::query()
            ->with(['translations', 'categories.translations'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('published_at')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (News $news): array => [
                'id' => $news->id,
                'status' => $news->status,
                'published_at' => $news->published_at?->toIso8601String(),
                'featured_until' => $news->featured_until?->toIso8601String(),
                'translations' => $news->translations->mapWithKeys(
                    fn (NewsTranslation $translation) => [$translation->locale => [
                        'title' => $translation->title,
                        'slug' => $translation->slug,
                    ]]
                ),
                'categories' => $news->categories->map(
                    fn (NewsCategory $category): array => [
                        'id' => $category->id,
                        'name' => $category->translation('en')?->name ?? $category->slug,
                    ]
                ),
            ]);

        return Inertia::render('cms/news/index', [
            'newsItems' => $newsItems,
            'filters' => [
                'status' => $filters['status'] ?? null,
            ],
            'status' => session('status'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', News::class);

        return Inertia::render('cms/news/create', [
            'categories' => $this->categories(),
            'availableStatuses' => $this->availableStatuses($request->user()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNewsRequest $request): RedirectResponse
    {
        $this->ensurePublishableStatus($request);

        $news = DB::transaction(function () use ($request): News {
            $news = News::query()->create([
                'status' => $request->validated('status'),
                'published_at' => $request->validated('published_at'),
                'archived_at' => $request->validated('archived_at'),
                'featured_until' => $request->validated('featured_until'),
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $this->syncTranslations($news, $request->validated('translations'));
            $news->categories()->sync($request->validated('category_ids', []));
            $this->syncCover($news, $request);
            $this->recordAudit($request, 'created', $news, null, $news->fresh()->toArray());

            return $news;
        });

        return to_route('cms.news.edit', $news)->with('status', 'news-created');
    }

    /**
     * Display the specified resource.
     */
    public function edit(News $news): Response
    {
        $this->authorize('update', $news);

        $news->load(['translations', 'categories']);
        $currentCover = $news->getFirstMedia('cover');

        return Inertia::render('cms/news/edit', [
            'newsItem' => [
                'id' => $news->id,
                'status' => $news->status,
                'published_at' => $news->published_at?->format('Y-m-d\\TH:i'),
                'archived_at' => $news->archived_at?->format('Y-m-d\\TH:i'),
                'featured_until' => $news->featured_until?->format('Y-m-d\\TH:i'),
                'category_ids' => $news->categories->pluck('id')->all(),
                'cover_url' => $news->getFirstMediaUrl('cover') ?: null,
                'current_cover' => $currentCover ? [
                    'id' => $currentCover->id,
                    'name' => $currentCover->name,
                    'url' => $currentCover->getUrl(),
                ] : null,
                'translations' => $news->translations->mapWithKeys(
                    fn (NewsTranslation $translation) => [$translation->locale => [
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
            'availableStatuses' => $this->availableStatuses(request()->user()),
            'canPublish' => request()->user()?->getAllPermissions()->contains('name', 'news.publish') ?? false,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNewsRequest $request, News $news): RedirectResponse
    {
        $this->ensurePublishableStatus($request);

        DB::transaction(function () use ($request, $news): void {
            $oldValues = $news->fresh()->toArray();

            $news->update([
                'status' => $request->validated('status'),
                'published_at' => $request->validated('published_at'),
                'archived_at' => $request->validated('archived_at'),
                'featured_until' => $request->validated('featured_until'),
                'updated_by' => $request->user()->id,
            ]);

            $this->syncTranslations($news, $request->validated('translations'));
            $news->categories()->sync($request->validated('category_ids', []));
            $this->syncCover($news, $request);
            $this->recordAudit($request, 'updated', $news, $oldValues, $news->fresh()->toArray());
        });

        return to_route('cms.news.edit', $news)->with('status', 'news-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, News $news): RedirectResponse
    {
        $this->authorize('delete', $news);

        $oldValues = $news->toArray();

        $news->delete();

        $this->recordAudit($request, 'deleted', $news, $oldValues, null);

        return to_route('cms.news.index')->with('status', 'news-deleted');
    }

    public function workflow(UpdateNewsWorkflowRequest $request, News $news): RedirectResponse
    {
        $newsId = $request->route('news') instanceof News
            ? $request->route('news')->getKey()
            : $request->route('news');

        $news = News::query()->findOrFail($newsId);
        $oldValues = $news->toArray();
        $status = $request->string('status')->toString();

        News::query()->whereKey($news->getKey())->update([
            'status' => $status,
            'published_at' => $status === 'published'
                ? ($news->published_at ?? now())
                : $news->published_at,
            'archived_at' => $status === 'archived' ? now() : null,
            'updated_by' => $request->user()->id,
        ]);

        $this->recordAudit($request, 'workflow-updated', $news, $oldValues, $news->refresh()->toArray());

        return back()->with('status', 'news-workflow-updated');
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    protected function syncTranslations(News $news, array $translations): void
    {
        foreach ($translations as $locale => $translation) {
            $news->translations()->updateOrCreate(
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

    protected function syncCover(News $news, Request $request): void
    {
        if ($request->boolean('remove_cover')) {
            $news->clearMediaCollection('cover');
        }

        if ($request->hasFile('cover')) {
            $news->clearMediaCollection('cover');
            $news->addMediaFromRequest('cover')->toMediaCollection('cover');
        }
    }

    protected function ensurePublishableStatus(Request $request): void
    {
        if (
            in_array($request->input('status'), ['published', 'archived'], true)
        ) {
            $this->authorize('publish', News::class);
        }
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    protected function categories(): array
    {
        return NewsCategory::query()
            ->with('translations')
            ->where('is_active', true)
            ->orderBy('slug')
            ->get()
            ->map(fn (NewsCategory $category): array => [
                'id' => $category->id,
                'name' => $category->translation('en')?->name ?? $category->slug,
            ])
            ->all();
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    protected function availableStatuses(?User $user): array
    {
        $statuses = $user?->getAllPermissions()->contains('name', 'news.publish')
            ? ['draft', 'in_review', 'published', 'archived']
            : ['draft', 'in_review'];

        return collect($statuses)
            ->map(fn (string $status): array => [
                'value' => $status,
                'label' => str($status)->replace('_', ' ')->title()->value(),
            ])
            ->all();
    }

    protected function recordAudit(
        Request $request,
        string $event,
        News $news,
        ?array $oldValues,
        ?array $newValues,
    ): void {
        AuditLog::query()->create([
            'user_id' => $request->user()?->id,
            'event' => $event,
            'auditable_type' => $news->getMorphClass(),
            'auditable_id' => $news->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

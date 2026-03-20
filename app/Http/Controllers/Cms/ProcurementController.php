<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterProcurementsRequest;
use App\Http\Requests\StoreProcurementRequest;
use App\Http\Requests\UpdateProcurementRequest;
use App\Http\Requests\UpdateProcurementWorkflowRequest;
use App\Models\AuditLog;
use App\Models\Procurement;
use App\Models\ProcurementTranslation;
use App\Models\User;
use App\Support\ContentBlocks\ContentBlockRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProcurementController extends Controller
{
    public function index(FilterProcurementsRequest $request): Response
    {
        $this->authorize('viewAny', Procurement::class);

        $filters = $request->validated();

        $procurements = Procurement::query()
            ->with('translations')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('published_at')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Procurement $procurement): array => [
                'id' => $procurement->id,
                'reference_number' => $procurement->reference_number,
                'procurement_type' => $procurement->procurement_type,
                'status' => $procurement->status,
                'published_at' => $procurement->published_at?->toIso8601String(),
                'closing_at' => $procurement->closing_at?->toIso8601String(),
                'translations' => $procurement->translations->mapWithKeys(
                    fn (ProcurementTranslation $translation) => [$translation->locale => [
                        'title' => $translation->title,
                        'slug' => $translation->slug,
                    ]]
                ),
            ]);

        return Inertia::render('cms/procurements/index', [
            'procurements' => $procurements,
            'filters' => [
                'status' => $filters['status'] ?? null,
            ],
            'status' => session('status'),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Procurement::class);

        return Inertia::render('cms/procurements/create', [
            'availableStatuses' => $this->availableStatuses($request->user()),
        ]);
    }

    public function store(StoreProcurementRequest $request): RedirectResponse
    {
        $procurement = DB::transaction(function () use ($request): Procurement {
            $procurement = Procurement::query()->create([
                'reference_number' => $request->validated('reference_number'),
                'procurement_type' => $request->validated('procurement_type'),
                'status' => $request->validated('status'),
                'published_at' => $request->validated('published_at'),
                'closing_at' => $request->validated('closing_at'),
                'archived_at' => $request->validated('archived_at'),
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $this->syncTranslations($procurement, $request->validated('translations'));
            $this->syncAttachments($procurement, $request);
            $this->recordAudit($request, 'created', $procurement, null, $procurement->fresh()->toArray());

            return $procurement;
        });

        return to_route('cms.procurements.edit', $procurement)->with('status', 'procurement-created');
    }

    public function edit(Procurement $procurement): Response
    {
        $this->authorize('update', $procurement);

        $procurement->load('translations');

        return Inertia::render('cms/procurements/edit', [
            'procurement' => [
                'id' => $procurement->id,
                'reference_number' => $procurement->reference_number,
                'procurement_type' => $procurement->procurement_type,
                'status' => $procurement->status,
                'published_at' => $procurement->published_at?->format('Y-m-d\\TH:i'),
                'closing_at' => $procurement->closing_at?->format('Y-m-d\\TH:i'),
                'archived_at' => $procurement->archived_at?->format('Y-m-d\\TH:i'),
                'attachments' => $procurement->getMedia('attachments')->map(fn ($media): array => [
                    'id' => $media->id,
                    'name' => $media->name,
                    'url' => $media->getUrl(),
                ])->values()->all(),
                'translations' => $procurement->translations->mapWithKeys(
                    fn (ProcurementTranslation $translation) => [$translation->locale => [
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
            'availableStatuses' => $this->availableStatuses(request()->user()),
            'canPublish' => request()->user()?->getAllPermissions()->contains('name', 'procurements.publish') ?? false,
            'status' => session('status'),
        ]);
    }

    public function update(
        UpdateProcurementRequest $request,
        Procurement $procurement,
    ): RedirectResponse {
        DB::transaction(function () use ($request, $procurement): void {
            $oldValues = $procurement->fresh()->toArray();

            $procurement->update([
                'reference_number' => $request->validated('reference_number'),
                'procurement_type' => $request->validated('procurement_type'),
                'status' => $request->validated('status'),
                'published_at' => $request->validated('published_at'),
                'closing_at' => $request->validated('closing_at'),
                'archived_at' => $request->validated('archived_at'),
                'updated_by' => $request->user()->id,
            ]);

            $this->syncTranslations($procurement, $request->validated('translations'));
            $this->syncAttachments($procurement, $request);
            $this->recordAudit($request, 'updated', $procurement, $oldValues, $procurement->fresh()->toArray());
        });

        return to_route('cms.procurements.edit', $procurement)->with('status', 'procurement-updated');
    }

    public function destroy(
        Request $request,
        Procurement $procurement,
    ): RedirectResponse {
        $this->authorize('delete', $procurement);

        $oldValues = $procurement->toArray();

        $procurement->delete();

        $this->recordAudit($request, 'deleted', $procurement, $oldValues, null);

        return to_route('cms.procurements.index')->with('status', 'procurement-deleted');
    }

    public function workflow(UpdateProcurementWorkflowRequest $request, Procurement $procurement): RedirectResponse
    {
        $procurementId = $request->route('procurement') instanceof Procurement
            ? $request->route('procurement')->getKey()
            : $request->route('procurement');

        $procurement = Procurement::query()->findOrFail($procurementId);
        $oldValues = $procurement->toArray();
        $status = $request->string('status')->toString();

        Procurement::query()->whereKey($procurement->getKey())->update([
            'status' => $status,
            'published_at' => in_array($status, ['open', 'closed', 'awarded'], true)
                ? ($procurement->published_at ?? now())
                : $procurement->published_at,
            'archived_at' => $status === 'archived' ? now() : null,
            'updated_by' => $request->user()->id,
        ]);

        $this->recordAudit(
            $request,
            'workflow-updated',
            $procurement,
            $oldValues,
            $procurement->refresh()->toArray(),
        );

        return back()->with('status', 'procurement-workflow-updated');
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    protected function syncTranslations(
        Procurement $procurement,
        array $translations,
    ): void {
        foreach ($translations as $locale => $translation) {
            $procurement->translations()->updateOrCreate(
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

    protected function syncAttachments(
        Procurement $procurement,
        Request $request,
    ): void {
        $attachmentIdsToRemove = collect($request->validated('remove_attachment_ids', []))
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        if ($attachmentIdsToRemove !== []) {
            $procurement->media()
                ->whereIn('id', $attachmentIdsToRemove)
                ->where('collection_name', 'attachments')
                ->get()
                ->each
                ->delete();
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                $procurement->addMedia($attachment)->toMediaCollection('attachments');
            }
        }
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    protected function availableStatuses(?User $user): array
    {
        $statuses = $user?->getAllPermissions()->contains('name', 'procurements.publish')
            ? ['planned', 'open', 'closed', 'awarded', 'cancelled', 'archived']
            : ['planned'];

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
        Procurement $procurement,
        ?array $oldValues,
        ?array $newValues,
    ): void {
        AuditLog::query()->create([
            'user_id' => $request->user()?->id,
            'event' => $event,
            'auditable_type' => $procurement->getMorphClass(),
            'auditable_id' => $procurement->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

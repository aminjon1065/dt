<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProcurementRequest;
use App\Http\Requests\UpdateProcurementRequest;
use App\Models\AuditLog;
use App\Models\Procurement;
use App\Models\ProcurementTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProcurementController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Procurement::class);

        $procurements = Procurement::query()
            ->with('translations')
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
            'status' => session('status'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Procurement::class);

        return Inertia::render('cms/procurements/create');
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
                        'seo_title' => $translation->seo_title,
                        'seo_description' => $translation->seo_description,
                    ]]
                ),
            ],
            'status' => session('status'),
        ]);
    }

    public function update(
        UpdateProcurementRequest $request,
        Procurement $procurement,
    ): RedirectResponse
    {
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
    ): RedirectResponse
    {
        $this->authorize('delete', $procurement);

        $oldValues = $procurement->toArray();

        $procurement->delete();

        $this->recordAudit($request, 'deleted', $procurement, $oldValues, null);

        return to_route('cms.procurements.index')->with('status', 'procurement-deleted');
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
                    'content' => $translation['content'] ?? null,
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
        if (! $request->hasFile('attachments')) {
            return;
        }

        $procurement->clearMediaCollection('attachments');

        foreach ($request->file('attachments') as $attachment) {
            $procurement->addMedia($attachment)->toMediaCollection('attachments');
        }
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

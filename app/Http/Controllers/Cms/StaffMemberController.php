<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterStaffMembersRequest;
use App\Http\Requests\StoreStaffMemberRequest;
use App\Http\Requests\UpdateStaffMemberRequest;
use App\Models\AuditLog;
use App\Models\StaffMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StaffMemberController extends Controller
{
    public function index(FilterStaffMembersRequest $request): Response
    {
        $this->authorize('viewAny', StaffMember::class);

        $filters = $request->validated();

        $staffMembers = StaffMember::query()
            ->with(['parent:id', 'translations'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['parent_id'] ?? null, fn ($query, $parentId) => $query->where('parent_id', $parentId))
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->whereHas('translations', function ($translationQuery) use ($search): void {
                    $translationQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (StaffMember $staffMember): array => [
                'id' => $staffMember->id,
                'parent_id' => $staffMember->parent_id,
                'parent_name' => $staffMember->parent?->translation('en')?->name,
                'email' => $staffMember->email,
                'phone' => $staffMember->phone,
                'status' => $staffMember->status,
                'published_at' => $staffMember->published_at?->toIso8601String(),
                'sort_order' => $staffMember->sort_order,
                'translations' => $staffMember->translations->mapWithKeys(
                    fn ($translation) => [$translation->locale => [
                        'name' => $translation->name,
                        'position' => $translation->position,
                    ]]
                ),
            ]);

        return Inertia::render('cms/staff-members/index', [
            'staffMembers' => $staffMembers,
            'filters' => [
                'search' => $filters['search'] ?? null,
                'status' => $filters['status'] ?? null,
                'parent_id' => isset($filters['parent_id']) ? (int) $filters['parent_id'] : null,
            ],
            'stats' => [
                'total' => StaffMember::query()->count(),
                'published' => StaffMember::query()->where('status', 'published')->count(),
                'draft' => StaffMember::query()->where('status', 'draft')->count(),
                'archived' => StaffMember::query()->where('status', 'archived')->count(),
            ],
            'parentStaffMembers' => $this->parentStaffMembers(),
            'status' => session('status'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', StaffMember::class);

        return Inertia::render('cms/staff-members/create', [
            'parentStaffMembers' => $this->parentStaffMembers(),
        ]);
    }

    public function store(StoreStaffMemberRequest $request): RedirectResponse
    {
        $staffMember = DB::transaction(function () use ($request): StaffMember {
            $staffMember = StaffMember::query()->create([
                'parent_id' => $request->validated('parent_id'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'office_location' => $request->validated('office_location'),
                'show_email_publicly' => $request->boolean('show_email_publicly'),
                'show_phone_publicly' => $request->boolean('show_phone_publicly'),
                'status' => $request->validated('status'),
                'published_at' => $request->validated('published_at'),
                'archived_at' => $request->validated('archived_at'),
                'sort_order' => $request->validated('sort_order'),
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $this->syncTranslations($staffMember, $request->validated('translations'));
            $this->syncPhoto($staffMember, $request);
            $this->recordAudit($request, 'created', $staffMember, null, $staffMember->fresh()->toArray());

            return $staffMember;
        });

        return to_route('cms.staff-members.edit', $staffMember)->with('status', 'staff-member-created');
    }

    public function edit(StaffMember $staffMember): Response
    {
        $this->authorize('update', $staffMember);

        $staffMember->load('translations');
        $currentPhoto = $staffMember->getFirstMedia('profile_photo');

        return Inertia::render('cms/staff-members/edit', [
            'staffMember' => [
                'id' => $staffMember->id,
                'parent_id' => $staffMember->parent_id,
                'email' => $staffMember->email,
                'phone' => $staffMember->phone,
                'office_location' => $staffMember->office_location,
                'show_email_publicly' => $staffMember->show_email_publicly,
                'show_phone_publicly' => $staffMember->show_phone_publicly,
                'status' => $staffMember->status,
                'published_at' => $staffMember->published_at?->format('Y-m-d\\TH:i'),
                'archived_at' => $staffMember->archived_at?->format('Y-m-d\\TH:i'),
                'sort_order' => $staffMember->sort_order,
                'current_photo' => $currentPhoto ? [
                    'id' => $currentPhoto->id,
                    'name' => $currentPhoto->name,
                    'url' => $currentPhoto->getUrl(),
                ] : null,
                'translations' => $staffMember->translations->mapWithKeys(
                    fn ($translation) => [$translation->locale => [
                        'name' => $translation->name,
                        'slug' => $translation->slug,
                        'position' => $translation->position,
                        'bio' => $translation->bio,
                        'seo_title' => $translation->seo_title,
                        'seo_description' => $translation->seo_description,
                    ]]
                ),
                'photo_url' => $staffMember->getFirstMediaUrl('profile_photo') ?: null,
            ],
            'parentStaffMembers' => $this->parentStaffMembers($staffMember),
            'status' => session('status'),
        ]);
    }

    public function update(UpdateStaffMemberRequest $request, StaffMember $staffMember): RedirectResponse
    {
        DB::transaction(function () use ($request, $staffMember): void {
            $oldValues = $staffMember->fresh()->toArray();

            $staffMember->update([
                'parent_id' => $request->validated('parent_id'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'office_location' => $request->validated('office_location'),
                'show_email_publicly' => $request->boolean('show_email_publicly'),
                'show_phone_publicly' => $request->boolean('show_phone_publicly'),
                'status' => $request->validated('status'),
                'published_at' => $request->validated('published_at'),
                'archived_at' => $request->validated('archived_at'),
                'sort_order' => $request->validated('sort_order'),
                'updated_by' => $request->user()->id,
            ]);

            $this->syncTranslations($staffMember, $request->validated('translations'));
            $this->syncPhoto($staffMember, $request);
            $this->recordAudit($request, 'updated', $staffMember, $oldValues, $staffMember->fresh()->toArray());
        });

        return to_route('cms.staff-members.edit', $staffMember)->with('status', 'staff-member-updated');
    }

    public function destroy(Request $request, StaffMember $staffMember): RedirectResponse
    {
        $this->authorize('delete', $staffMember);

        $oldValues = $staffMember->toArray();
        $staffMember->delete();
        $this->recordAudit($request, 'deleted', $staffMember, $oldValues, null);

        return to_route('cms.staff-members.index')->with('status', 'staff-member-deleted');
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    protected function syncTranslations(StaffMember $staffMember, array $translations): void
    {
        foreach ($translations as $locale => $translation) {
            $staffMember->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'name' => $translation['name'],
                    'slug' => $translation['slug'],
                    'position' => $translation['position'] ?? null,
                    'bio' => $translation['bio'] ?? null,
                    'seo_title' => $translation['seo_title'] ?? null,
                    'seo_description' => $translation['seo_description'] ?? null,
                ],
            );
        }
    }

    protected function syncPhoto(StaffMember $staffMember, Request $request): void
    {
        if ($request->boolean('remove_photo')) {
            $staffMember->clearMediaCollection('profile_photo');
        }

        if ($request->hasFile('photo')) {
            $staffMember->clearMediaCollection('profile_photo');
            $staffMember->addMediaFromRequest('photo')->toMediaCollection('profile_photo');
        }
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    protected function parentStaffMembers(?StaffMember $excludingStaffMember = null): array
    {
        return StaffMember::query()
            ->with('translations')
            ->when($excludingStaffMember, fn ($query) => $query->whereKeyNot($excludingStaffMember->id))
            ->orderBy('sort_order')
            ->get()
            ->map(fn (StaffMember $staffMember): array => [
                'id' => $staffMember->id,
                'name' => $staffMember->translation('en')?->name ?? "Staff Member #{$staffMember->id}",
            ])
            ->all();
    }

    protected function recordAudit(
        Request $request,
        string $event,
        StaffMember $staffMember,
        ?array $oldValues,
        ?array $newValues,
    ): void {
        AuditLog::query()->create([
            'user_id' => $request->user()?->id,
            'event' => $event,
            'auditable_type' => $staffMember->getMorphClass(),
            'auditable_id' => $staffMember->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

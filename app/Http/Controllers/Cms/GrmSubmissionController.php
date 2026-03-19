<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGrmSubmissionRequest;
use App\Http\Requests\UpdateGrmSubmissionRequest;
use App\Models\AuditLog;
use App\Models\GrmSubmission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GrmSubmissionController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', GrmSubmission::class);

        $submissions = GrmSubmission::query()
            ->with(['assignee:id,name', 'notes.user:id,name'])
            ->orderByDesc('submitted_at')
            ->get()
            ->map(fn (GrmSubmission $submission): array => [
                'id' => $submission->id,
                'reference_number' => $submission->reference_number,
                'name' => $submission->name,
                'subject' => $submission->subject,
                'status' => $submission->status,
                'submitted_at' => $submission->submitted_at?->toIso8601String(),
                'assignee' => $submission->assignee?->name,
                'notes_count' => $submission->notes->count(),
            ]);

        return Inertia::render('cms/grm-submissions/index', [
            'submissions' => $submissions,
            'status' => session('status'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', GrmSubmission::class);

        return Inertia::render('cms/grm-submissions/create', [
            'users' => $this->users(),
        ]);
    }

    public function store(StoreGrmSubmissionRequest $request): RedirectResponse
    {
        $submission = DB::transaction(function () use ($request): GrmSubmission {
            $submission = GrmSubmission::query()->create([
                'reference_number' => $request->validated('reference_number'),
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'subject' => $request->validated('subject'),
                'message' => $request->validated('message'),
                'status' => $request->validated('status'),
                'submitted_at' => $request->validated('submitted_at'),
                'reviewed_at' => $request->validated('reviewed_at'),
                'resolved_at' => $request->validated('resolved_at'),
                'assigned_to' => $request->validated('assigned_to'),
            ]);

            if ($request->filled('note')) {
                $submission->notes()->create([
                    'user_id' => $request->user()->id,
                    'note' => $request->validated('note'),
                ]);
            }

            $this->recordAudit($request, 'created', $submission, null, $submission->fresh()->toArray());

            return $submission;
        });

        return to_route('cms.grm-submissions.edit', $submission)->with('status', 'grm-submission-created');
    }

    public function edit(GrmSubmission $grmSubmission): Response
    {
        $this->authorize('update', $grmSubmission);

        $grmSubmission->load(['assignee:id,name', 'notes.user:id,name']);

        return Inertia::render('cms/grm-submissions/edit', [
            'submission' => [
                'id' => $grmSubmission->id,
                'reference_number' => $grmSubmission->reference_number,
                'name' => $grmSubmission->name,
                'email' => $grmSubmission->email,
                'phone' => $grmSubmission->phone,
                'subject' => $grmSubmission->subject,
                'message' => $grmSubmission->message,
                'status' => $grmSubmission->status,
                'submitted_at' => $grmSubmission->submitted_at?->format('Y-m-d\\TH:i'),
                'reviewed_at' => $grmSubmission->reviewed_at?->format('Y-m-d\\TH:i'),
                'resolved_at' => $grmSubmission->resolved_at?->format('Y-m-d\\TH:i'),
                'assigned_to' => $grmSubmission->assigned_to,
                'notes' => $grmSubmission->notes->map(fn ($note): array => [
                    'id' => $note->id,
                    'note' => $note->note,
                    'user' => $note->user?->name,
                    'created_at' => $note->created_at?->toIso8601String(),
                ])->values()->all(),
            ],
            'users' => $this->users(),
            'status' => session('status'),
        ]);
    }

    public function update(
        UpdateGrmSubmissionRequest $request,
        GrmSubmission $grmSubmission,
    ): RedirectResponse
    {
        DB::transaction(function () use ($request, $grmSubmission): void {
            $oldValues = $grmSubmission->fresh()->toArray();

            $grmSubmission->update([
                'reference_number' => $request->validated('reference_number'),
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'subject' => $request->validated('subject'),
                'message' => $request->validated('message'),
                'status' => $request->validated('status'),
                'submitted_at' => $request->validated('submitted_at'),
                'reviewed_at' => $request->validated('reviewed_at'),
                'resolved_at' => $request->validated('resolved_at'),
                'assigned_to' => $request->validated('assigned_to'),
            ]);

            if ($request->filled('note')) {
                $grmSubmission->notes()->create([
                    'user_id' => $request->user()->id,
                    'note' => $request->validated('note'),
                ]);
            }

            $this->recordAudit($request, 'updated', $grmSubmission, $oldValues, $grmSubmission->fresh()->toArray());
        });

        return to_route('cms.grm-submissions.edit', $grmSubmission)->with('status', 'grm-submission-updated');
    }

    public function destroy(
        Request $request,
        GrmSubmission $grmSubmission,
    ): RedirectResponse
    {
        $this->authorize('delete', $grmSubmission);

        $oldValues = $grmSubmission->toArray();

        $grmSubmission->delete();

        $this->recordAudit($request, 'deleted', $grmSubmission, $oldValues, null);

        return to_route('cms.grm-submissions.index')->with('status', 'grm-submission-deleted');
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    protected function users(): array
    {
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
            ])
            ->all();
    }

    protected function recordAudit(
        Request $request,
        string $event,
        GrmSubmission $submission,
        ?array $oldValues,
        ?array $newValues,
    ): void {
        AuditLog::query()->create([
            'user_id' => $request->user()?->id,
            'event' => $event,
            'auditable_type' => $submission->getMorphClass(),
            'auditable_id' => $submission->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

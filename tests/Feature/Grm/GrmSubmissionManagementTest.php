<?php

use App\Models\GrmSubmission;
use App\Models\User;
use Spatie\Permission\Models\Permission;

it('supports grm submissions with assignment and notes', function () {
    $user = User::factory()->create();
    $assignee = User::factory()->create();

    $submission = GrmSubmission::query()->create([
        'reference_number' => 'GRM-2026-001',
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+992900000000',
        'subject' => 'Service complaint',
        'message' => 'Detailed message',
        'status' => 'under_review',
        'submitted_at' => now(),
        'reviewed_at' => now(),
        'assigned_to' => $assignee->id,
    ]);

    $submission->notes()->create([
        'user_id' => $user->id,
        'note' => 'Initial review completed.',
    ]);

    expect($submission->assignee?->is($assignee))->toBeTrue()
        ->and($submission->notes)->toHaveCount(1)
        ->and($submission->notes->first()?->note)->toBe('Initial review completed.');
});

it('allows authorized users to view the grm index', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::query()->create([
        'name' => 'grm.view',
        'guard_name' => 'web',
    ]));

    $this->actingAs($user)
        ->get(route('cms.grm-submissions.index'))
        ->assertSuccessful();
});

it('stores a grm submission and note through the cms route', function () {
    $user = User::factory()->create();
    $assignee = User::factory()->create();
    $user->givePermissionTo(Permission::query()->create([
        'name' => 'grm.create',
        'guard_name' => 'web',
    ]));

    $response = $this
        ->withoutMiddleware()
        ->actingAs($user)
        ->post(route('cms.grm-submissions.store'), [
            'reference_number' => 'GRM-2026-010',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+992911111111',
            'subject' => 'Feedback',
            'message' => 'Feedback message',
            'status' => 'new',
            'submitted_at' => now()->format('Y-m-d H:i:s'),
            'assigned_to' => $assignee->id,
            'note' => 'Case created internally.',
        ]);

    $response->assertRedirect();

    expect(GrmSubmission::query()->count())->toBe(1)
        ->and(GrmSubmission::query()->first()?->notes()->count())->toBe(1)
        ->and(GrmSubmission::query()->first()?->assigned_to)->toBe($assignee->id);
});

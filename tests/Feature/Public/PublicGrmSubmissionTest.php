<?php

use App\Models\AuditLog;
use App\Models\GrmSubmission;
use Inertia\Testing\AssertableInertia;

it('renders the public grm submission page', function () {
    $this->get('/ru/grm')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/grm/create')
            ->where('locale', 'ru'));
});

it('stores a public grm submission and redirects to the thank-you page', function () {
    $response = $this
        ->withoutMiddleware()
        ->post('/tj/grm', [
            'name' => 'Фаридун',
            'email' => 'faridun@example.com',
            'phone' => '+992900001122',
            'subject' => 'Муроҷиат',
            'message' => 'Ин як муроҷиати пурра барои санҷиши шакли ҷамъиятӣ мебошад.',
        ]);

    $submission = GrmSubmission::query()->first();

    $response->assertRedirect(route('public.grm.thank-you', [
        'locale' => 'tj',
        'reference' => $submission?->reference_number,
    ]));

    expect($submission)->not->toBeNull()
        ->and($submission?->status)->toBe('new')
        ->and($submission?->subject)->toBe('Муроҷиат')
        ->and(AuditLog::query()->where('event', 'public-submitted')->count())->toBe(1);
});

it('renders the public grm thank-you page', function () {
    $this->get('/en/grm/thank-you?reference=GRM-2026-TEST1234')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/grm/thank-you')
            ->where('reference', 'GRM-2026-TEST1234'));
});

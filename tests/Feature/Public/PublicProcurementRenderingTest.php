<?php

use App\Models\Procurement;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('renders the public procurements listing', function () {
    $user = User::factory()->create();

    $procurement = Procurement::query()->create([
        'reference_number' => 'RFQ-2026-100',
        'procurement_type' => 'services',
        'status' => 'open',
        'published_at' => now(),
        'closing_at' => now()->addWeek(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $procurement->translations()->createMany([
        [
            'locale' => 'en',
            'title' => 'Consulting services',
            'slug' => 'consulting-services',
            'summary' => 'Summary',
            'content' => '<p>Content</p>',
        ],
        [
            'locale' => 'ru',
            'title' => 'Консультационные услуги',
            'slug' => 'konsultacionnye-uslugi',
            'summary' => 'Кратко',
            'content' => '<p>Контент</p>',
        ],
    ]);

    $this->get('/ru/procurements')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/procurements/index')
            ->where('procurements.data.0.title', 'Консультационные услуги'));
});

it('filters the public procurements listing by search, status, and type', function () {
    $user = User::factory()->create();

    $matchingProcurement = Procurement::query()->create([
        'reference_number' => 'RFQ-2026-300',
        'procurement_type' => 'services',
        'status' => 'open',
        'published_at' => now(),
        'closing_at' => now()->addWeek(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $matchingProcurement->translations()->create([
        'locale' => 'en',
        'title' => 'Audit consulting services',
        'slug' => 'audit-consulting-services',
        'summary' => 'Consulting summary',
        'content' => '<p>Consulting content</p>',
    ]);

    $nonMatchingProcurement = Procurement::query()->create([
        'reference_number' => 'RFQ-2026-301',
        'procurement_type' => 'goods',
        'status' => 'closed',
        'published_at' => now(),
        'closing_at' => now()->addWeek(),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $nonMatchingProcurement->translations()->create([
        'locale' => 'en',
        'title' => 'Office equipment supply',
        'slug' => 'office-equipment-supply',
        'summary' => 'Supply summary',
        'content' => '<p>Supply content</p>',
    ]);

    $this->get('/en/procurements?search=Audit&status=open&procurement_type=services')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/procurements/index')
            ->where('filters.search', 'Audit')
            ->where('filters.status', 'open')
            ->where('filters.procurement_type', 'services')
            ->has('procurements.data', 1)
            ->where('procurements.data.0.title', 'Audit consulting services'));
});

it('renders a public procurement detail page by localized slug', function () {
    $user = User::factory()->create();

    $procurement = Procurement::query()->create([
        'reference_number' => 'RFQ-2026-200',
        'procurement_type' => 'goods',
        'status' => 'closed',
        'published_at' => now(),
        'closing_at' => now()->addDays(3),
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $procurement->translations()->createMany([
        [
            'locale' => 'en',
            'title' => 'Equipment supply',
            'slug' => 'equipment-supply',
            'summary' => 'Summary',
            'content' => '<p>Content</p>',
        ],
        [
            'locale' => 'tj',
            'title' => 'Таъминоти таҷҳизот',
            'slug' => 'taminoti-tajhizot',
            'summary' => 'Хулоса',
            'content' => '<p>Матн</p>',
        ],
    ]);

    $this->get('/tj/procurements/taminoti-tajhizot')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/procurements/show')
            ->where('procurement.title', 'Таъминоти таҷҳизот'));
});

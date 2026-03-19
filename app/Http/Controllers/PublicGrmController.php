<?php

namespace App\Http\Controllers;

use App\Http\Requests\Public\StorePublicGrmSubmissionRequest;
use App\Models\AuditLog;
use App\Models\GrmSubmission;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PublicGrmController extends Controller
{
    public function create(string $locale): Response
    {
        return Inertia::render('public/grm/create', [
            'locale' => $locale,
            'site' => $this->siteData($locale),
            'navigation' => $this->navigation($locale),
            'seo' => [
                'title' => 'GRM and feedback',
                'description' => 'Submit a grievance, complaint, or public feedback through the official portal.',
                'canonical_url' => route('public.grm.create', ['locale' => $locale]),
                'type' => 'website',
            ],
            'structuredData' => [[
                '@context' => 'https://schema.org',
                '@type' => 'ContactPage',
                'name' => 'GRM and feedback',
                'url' => route('public.grm.create', ['locale' => $locale]),
            ]],
        ]);
    }

    public function store(
        StorePublicGrmSubmissionRequest $request,
        string $locale,
    ): RedirectResponse {
        $submission = DB::transaction(function () use ($request): GrmSubmission {
            $submission = GrmSubmission::query()->create([
                'reference_number' => $this->referenceNumber(),
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'subject' => $request->validated('subject'),
                'message' => $request->validated('message'),
                'status' => 'new',
                'submitted_at' => now(),
            ]);

            AuditLog::query()->create([
                'user_id' => null,
                'event' => 'public-submitted',
                'auditable_type' => $submission->getMorphClass(),
                'auditable_id' => $submission->id,
                'old_values' => null,
                'new_values' => $submission->fresh()->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $submission;
        });

        return to_route('public.grm.thank-you', [
            'locale' => $locale,
            'reference' => $submission->reference_number,
        ]);
    }

    public function thankYou(Request $request, string $locale): Response
    {
        return Inertia::render('public/grm/thank-you', [
            'locale' => $locale,
            'reference' => $request->string('reference')->toString(),
            'site' => $this->siteData($locale),
            'navigation' => $this->navigation($locale),
            'seo' => [
                'title' => 'Submission received',
                'description' => 'Your grievance or feedback has been submitted successfully.',
                'canonical_url' => route('public.grm.thank-you', ['locale' => $locale, 'reference' => $request->string('reference')->toString()]),
                'robots' => 'noindex,nofollow',
                'type' => 'website',
            ],
        ]);
    }

    protected function siteData(string $locale): array
    {
        return [
            'name' => Setting::for('site', 'name', config('app.name')),
            'tagline' => Setting::for('site', 'tagline'),
            'default_locale' => Setting::for('site', 'default_locale', 'en'),
            'contact_email' => Setting::for('contact', 'email'),
            'contact_phone' => Setting::for('contact', 'phone'),
            'contact_address' => Setting::for('contact', 'address'),
            'locale' => $locale,
        ];
    }

    protected function navigation(string $locale): array
    {
        return app(PublicPageController::class)->navigation($locale);
    }

    protected function referenceNumber(): string
    {
        do {
            $reference = 'GRM-'.now()->format('Y').'-'.Str::upper(Str::random(8));
        } while (GrmSubmission::query()->where('reference_number', $reference)->exists());

        return $reference;
    }
}

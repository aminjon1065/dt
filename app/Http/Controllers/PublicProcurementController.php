<?php

namespace App\Http\Controllers;

use App\Models\Procurement;
use App\Models\Setting;
use Inertia\Inertia;
use Inertia\Response;

class PublicProcurementController extends Controller
{
    public function index(string $locale): Response
    {
        $procurements = Procurement::query()
            ->with('translations')
            ->whereIn('status', ['open', 'closed', 'awarded'])
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderByDesc('published_at')
            ->orderByDesc('closing_at')
            ->get()
            ->map(fn (Procurement $procurement): array => $this->transformProcurementListItem($procurement, $locale))
            ->values()
            ->all();

        return Inertia::render('public/procurements/index', [
            'locale' => $locale,
            'site' => $this->siteData($locale),
            'navigation' => app(PublicPageController::class)->navigation($locale),
            'seo' => [
                'title' => 'Procurements',
                'description' => 'Current and recent procurement notices and tender information.',
                'canonical_url' => route('public.procurements.index', ['locale' => $locale]),
                'type' => 'website',
            ],
            'procurements' => $procurements,
        ]);
    }

    public function show(string $locale, string $slug): Response
    {
        $procurement = Procurement::query()
            ->with('translations')
            ->whereIn('status', ['open', 'closed', 'awarded'])
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->whereHas('translations', function ($query) use ($locale, $slug): void {
                $query->where('locale', $locale)->where('slug', $slug);
            })
            ->firstOrFail();

        return Inertia::render('public/procurements/show', [
            'locale' => $locale,
            'site' => $this->siteData($locale),
            'navigation' => app(PublicPageController::class)->navigation($locale),
            'seo' => [
                'title' => $this->transformProcurement($procurement, $locale)['title'],
                'description' => $this->transformProcurement($procurement, $locale)['summary'],
                'canonical_url' => route('public.procurements.show', ['locale' => $locale, 'slug' => $slug]),
                'type' => 'article',
            ],
            'structuredData' => [[
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $this->transformProcurement($procurement, $locale)['title'],
                'description' => $this->transformProcurement($procurement, $locale)['summary'],
                'datePublished' => $procurement->published_at?->toIso8601String(),
                'url' => route('public.procurements.show', ['locale' => $locale, 'slug' => $slug]),
            ]],
            'procurement' => $this->transformProcurement($procurement, $locale),
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

    protected function transformProcurementListItem(Procurement $procurement, string $locale): array
    {
        $translation = $procurement->translation($locale) ?? $procurement->translation('en');

        return [
            'id' => $procurement->id,
            'reference_number' => $procurement->reference_number,
            'procurement_type' => $procurement->procurement_type,
            'status' => $procurement->status,
            'published_at' => $procurement->published_at?->toDateString(),
            'closing_at' => $procurement->closing_at?->toDateString(),
            'title' => $translation?->title,
            'slug' => $translation?->slug,
            'summary' => $translation?->summary,
        ];
    }

    protected function transformProcurement(Procurement $procurement, string $locale): array
    {
        $translation = $procurement->translation($locale) ?? $procurement->translation('en');

        abort_unless($translation !== null, 404);

        return [
            'id' => $procurement->id,
            'reference_number' => $procurement->reference_number,
            'procurement_type' => $procurement->procurement_type,
            'status' => $procurement->status,
            'published_at' => $procurement->published_at?->toDateString(),
            'closing_at' => $procurement->closing_at?->toDateString(),
            'title' => $translation->title,
            'slug' => $translation->slug,
            'summary' => $translation->summary,
            'content' => $translation->content,
            'attachments' => $procurement->getMedia('attachments')->map(fn ($media): array => [
                'id' => $media->id,
                'name' => $media->name,
                'url' => $media->getUrl(),
            ])->values()->all(),
        ];
    }
}

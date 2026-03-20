<?php

namespace App\Http\Controllers;

use App\Http\Requests\Public\FilterPublicProcurementsRequest;
use App\Models\Procurement;
use App\Models\Setting;
use Inertia\Inertia;
use Inertia\Response;

class PublicProcurementController extends Controller
{
    public function index(FilterPublicProcurementsRequest $request, string $locale): Response
    {
        $filters = $request->validated();

        $procurements = Procurement::query()
            ->with('translations')
            ->whereIn('status', ['open', 'closed', 'awarded'])
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });

        if (($search = $filters['search'] ?? null) !== null) {
            $procurements->where(function ($query) use ($locale, $search): void {
                $query->where('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('translations', function ($translationQuery) use ($locale, $search): void {
                        $translationQuery->whereIn('locale', [$locale, 'en'])
                            ->where(function ($localizedQuery) use ($search): void {
                                $localizedQuery->where('title', 'like', "%{$search}%")
                                    ->orWhere('summary', 'like', "%{$search}%")
                                    ->orWhere('content', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if (($status = $filters['status'] ?? null) !== null) {
            $procurements->where('status', $status);
        }

        if (($procurementType = $filters['procurement_type'] ?? null) !== null) {
            $procurements->where('procurement_type', $procurementType);
        }

        $procurements = $procurements
            ->orderByDesc('published_at')
            ->orderByDesc('closing_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Procurement $procurement): array => $this->transformProcurementListItem($procurement, $locale));

        return Inertia::render('public/procurements/index', [
            'locale' => $locale,
            'site' => $this->siteData($locale),
            'navigation' => app(PublicPageController::class)->navigation($locale),
            'indexUrl' => route('public.procurements.index', ['locale' => $locale]),
            'seo' => [
                'title' => 'Procurements',
                'description' => 'Current and recent procurement notices and tender information.',
                'canonical_url' => route('public.procurements.index', ['locale' => $locale]),
                'type' => 'website',
            ],
            'filters' => [
                'search' => $filters['search'] ?? null,
                'status' => $filters['status'] ?? null,
                'procurement_type' => $filters['procurement_type'] ?? null,
            ],
            'statuses' => [
                ['value' => 'open', 'label' => 'Open'],
                ['value' => 'closed', 'label' => 'Closed'],
                ['value' => 'awarded', 'label' => 'Awarded'],
            ],
            'procurementTypes' => Procurement::query()
                ->whereIn('status', ['open', 'closed', 'awarded'])
                ->whereNotNull('procurement_type')
                ->distinct()
                ->orderBy('procurement_type')
                ->pluck('procurement_type')
                ->values()
                ->all(),
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

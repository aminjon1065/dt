<?php

namespace App\Http\Controllers;

use App\Http\Requests\Public\FilterPublicSearchRequest;
use App\Models\Document;
use App\Models\News;
use App\Models\Page;
use App\Models\Procurement;
use App\Models\Setting;
use App\Models\StaffMember;
use Inertia\Inertia;
use Inertia\Response;

class PublicSearchController extends Controller
{
    public function __invoke(FilterPublicSearchRequest $request, string $locale): Response
    {
        $query = trim((string) ($request->validated()['q'] ?? ''));

        return Inertia::render('public/search/index', [
            'locale' => $locale,
            'site' => $this->siteData($locale),
            'navigation' => app(PublicPageController::class)->navigation($locale),
            'seo' => [
                'title' => $query !== '' ? "Search results for {$query}" : 'Search',
                'description' => 'Search across pages, news, documents, procurement notices, and staff.',
                'canonical_url' => route('public.search', ['locale' => $locale]),
                'robots' => 'noindex,follow',
                'type' => 'website',
            ],
            'searchUrl' => route('public.search', ['locale' => $locale]),
            'filters' => [
                'q' => $query !== '' ? $query : null,
            ],
            'results' => [
                'pages' => $query !== '' ? $this->searchPages($locale, $query) : [],
                'news' => $query !== '' ? $this->searchNews($locale, $query) : [],
                'documents' => $query !== '' ? $this->searchDocuments($locale, $query) : [],
                'procurements' => $query !== '' ? $this->searchProcurements($locale, $query) : [],
                'staff' => $query !== '' ? $this->searchStaff($locale, $query) : [],
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

    protected function searchPages(string $locale, string $query): array
    {
        return Page::query()
            ->with('translations')
            ->where('status', 'published')
            ->where(function ($builder): void {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->whereHas('translations', function ($builder) use ($locale, $query): void {
                $builder->where('locale', $locale)
                    ->where(function ($translationQuery) use ($query): void {
                        $translationQuery->where('title', 'like', "%{$query}%")
                            ->orWhere('summary', 'like', "%{$query}%")
                            ->orWhere('content', 'like', "%{$query}%");
                    });
            })
            ->orderByDesc('is_home')
            ->orderBy('sort_order')
            ->limit(5)
            ->get()
            ->map(function (Page $page) use ($locale): array {
                $translation = $page->translation($locale);

                return [
                    'id' => $page->id,
                    'title' => $translation?->title,
                    'summary' => $translation?->summary,
                    'href' => $page->is_home
                        ? route('public.home', ['locale' => $locale])
                        : route('public.pages.show', ['locale' => $locale, 'slug' => $translation?->slug]),
                    'meta' => $page->is_home ? 'Page · Home' : 'Page',
                ];
            })
            ->values()
            ->all();
    }

    protected function searchNews(string $locale, string $query): array
    {
        return News::query()
            ->with('translations')
            ->where('status', 'published')
            ->where(function ($builder): void {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->whereHas('translations', function ($builder) use ($locale, $query): void {
                $builder->where('locale', $locale)
                    ->where(function ($translationQuery) use ($query): void {
                        $translationQuery->where('title', 'like', "%{$query}%")
                            ->orWhere('summary', 'like', "%{$query}%")
                            ->orWhere('content', 'like', "%{$query}%");
                    });
            })
            ->orderByDesc('featured_until')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get()
            ->map(function (News $news) use ($locale): array {
                $translation = $news->translation($locale);

                return [
                    'id' => $news->id,
                    'title' => $translation?->title,
                    'summary' => $translation?->summary,
                    'href' => route('public.news.show', ['locale' => $locale, 'slug' => $translation?->slug]),
                    'meta' => 'News'.($news->published_at ? ' · '.$news->published_at->toDateString() : ''),
                ];
            })
            ->values()
            ->all();
    }

    protected function searchDocuments(string $locale, string $query): array
    {
        return Document::query()
            ->with('translations')
            ->where('status', 'published')
            ->where(function ($builder): void {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->whereHas('translations', function ($builder) use ($locale, $query): void {
                $builder->where('locale', $locale)
                    ->where(function ($translationQuery) use ($query): void {
                        $translationQuery->where('title', 'like', "%{$query}%")
                            ->orWhere('summary', 'like', "%{$query}%");
                    });
            })
            ->orderByDesc('document_date')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get()
            ->map(function (Document $document) use ($locale): array {
                $translation = $document->translation($locale);

                return [
                    'id' => $document->id,
                    'title' => $translation?->title,
                    'summary' => $translation?->summary,
                    'href' => route('public.documents.show', ['locale' => $locale, 'slug' => $translation?->slug]),
                    'meta' => 'Document'.($document->file_type ? ' · '.strtoupper($document->file_type) : ''),
                ];
            })
            ->values()
            ->all();
    }

    protected function searchProcurements(string $locale, string $query): array
    {
        return Procurement::query()
            ->with('translations')
            ->whereIn('status', ['open', 'closed', 'awarded'])
            ->where(function ($builder): void {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where(function ($builder) use ($locale, $query): void {
                $builder->where('reference_number', 'like', "%{$query}%")
                    ->orWhereHas('translations', function ($translationBuilder) use ($locale, $query): void {
                        $translationBuilder->where('locale', $locale)
                            ->where(function ($translationQuery) use ($query): void {
                                $translationQuery->where('title', 'like', "%{$query}%")
                                    ->orWhere('summary', 'like', "%{$query}%")
                                    ->orWhere('content', 'like', "%{$query}%");
                            });
                    });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('closing_at')
            ->limit(5)
            ->get()
            ->map(function (Procurement $procurement) use ($locale): array {
                $translation = $procurement->translation($locale);

                return [
                    'id' => $procurement->id,
                    'title' => $translation?->title,
                    'summary' => $translation?->summary,
                    'href' => route('public.procurements.show', ['locale' => $locale, 'slug' => $translation?->slug]),
                    'meta' => 'Procurement · '.strtoupper($procurement->status),
                ];
            })
            ->values()
            ->all();
    }

    protected function searchStaff(string $locale, string $query): array
    {
        return StaffMember::query()
            ->with('translations')
            ->where('status', 'published')
            ->where(function ($builder): void {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->whereHas('translations', function ($builder) use ($locale, $query): void {
                $builder->where('locale', $locale)
                    ->where(function ($translationQuery) use ($query): void {
                        $translationQuery->where('name', 'like', "%{$query}%")
                            ->orWhere('position', 'like', "%{$query}%")
                            ->orWhere('bio', 'like', "%{$query}%");
                    });
            })
            ->orderBy('sort_order')
            ->limit(5)
            ->get()
            ->map(function (StaffMember $staffMember) use ($locale): array {
                $translation = $staffMember->translation($locale);

                return [
                    'id' => $staffMember->id,
                    'title' => $translation?->name,
                    'summary' => $translation?->position,
                    'href' => route('public.staff.show', ['locale' => $locale, 'slug' => $translation?->slug]),
                    'meta' => 'Staff',
                ];
            })
            ->values()
            ->all();
    }
}

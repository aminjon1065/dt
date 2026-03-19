<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\News;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Procurement;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PublicPageController extends Controller
{
    public function redirectToDefaultLocale(): RedirectResponse
    {
        $defaultLocale = Setting::for('site', 'default_locale', 'en');

        return to_route('public.home', ['locale' => $defaultLocale]);
    }

    public function home(string $locale): Response
    {
        $page = Page::query()
            ->with(['translations', 'children.translations'])
            ->where('status', 'published')
            ->where('is_home', true)
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->first();

        return Inertia::render('public/home', [
            'locale' => $locale,
            'page' => $page ? $this->transformPage($page, $locale) : null,
            'site' => $this->siteData($locale),
            'navigation' => $this->navigation($locale),
            'seo' => $this->homeSeo($locale, $page),
            'structuredData' => $this->homeStructuredData($locale, $page),
            'latestNews' => $this->latestNews($locale),
            'latestDocuments' => $this->latestDocuments($locale),
            'latestProcurements' => $this->latestProcurements($locale),
        ]);
    }

    public function show(string $locale, string $slug): Response
    {
        $page = Page::query()
            ->with(['translations', 'children.translations'])
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->whereHas('translations', function ($query) use ($locale, $slug): void {
                $query->where('locale', $locale)->where('slug', $slug);
            })
            ->firstOrFail();

        return Inertia::render('public/page', [
            'locale' => $locale,
            'page' => $this->transformPage($page, $locale),
            'site' => $this->siteData($locale),
            'navigation' => $this->navigation($locale),
            'seo' => $this->pageSeo($locale, $page),
            'structuredData' => [
                $this->webPageSchema(
                    route('public.pages.show', [
                        'locale' => $locale,
                        'slug' => $page->translation($locale)?->slug ?? $page->translation('en')?->slug,
                    ]),
                    $page->translation($locale)?->title ?? $page->translation('en')?->title ?? config('app.name'),
                    $page->translation($locale)?->summary
                        ?? $page->translation($locale)?->seo_description
                        ?? $page->translation('en')?->summary
                        ?? $page->translation('en')?->seo_description
                ),
            ],
            'isHome' => false,
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

    public function navigation(string $locale): array
    {
        $menu = Menu::query()
            ->with([
                'items' => fn ($query) => $query
                    ->whereNull('parent_id')
                    ->where('is_active', true)
                    ->where(fn ($itemQuery) => $itemQuery->whereNull('locale')->orWhere('locale', $locale))
                    ->with([
                        'children' => fn ($childrenQuery) => $childrenQuery
                            ->where('is_active', true)
                            ->where(fn ($itemQuery) => $itemQuery->whereNull('locale')->orWhere('locale', $locale)),
                    ]),
            ])
            ->where('location', 'main')
            ->first();

        if (! $menu) {
            return [];
        }

        return $menu->items->map(fn ($item): array => [
            'id' => $item->id,
            'label' => $item->label,
            'href' => $item->url ?: ($item->route_name && \Route::has($item->route_name) ? route($item->route_name) : '#'),
            'children' => $item->children->map(fn ($child): array => [
                'id' => $child->id,
                'label' => $child->label,
                'href' => $child->url ?: ($child->route_name && \Route::has($child->route_name) ? route($child->route_name) : '#'),
            ])->values()->all(),
        ])->values()->all();
    }

    protected function transformPage(Page $page, string $locale): array
    {
        $translation = $page->translation($locale) ?? $page->translation('en');

        abort_unless($translation !== null, 404);

        return [
            'id' => $page->id,
            'title' => $translation->title,
            'slug' => $translation->slug,
            'summary' => $translation->summary,
            'content' => $translation->content,
            'seo_title' => $translation->seo_title,
            'seo_description' => $translation->seo_description,
            'cover_url' => $page->getFirstMediaUrl('cover') ?: null,
            'children' => $page->children->map(function (Page $child) use ($locale): array {
                $translation = $child->translation($locale) ?? $child->translation('en');

                return [
                    'id' => $child->id,
                    'title' => $translation?->title,
                    'slug' => $translation?->slug,
                ];
            })->filter(fn (array $child): bool => filled($child['slug']))->values()->all(),
        ];
    }

    /**
     * @return array<int, array{id:int,title:?string,slug:?string,summary:?string,published_at:?string}>
     */
    protected function latestNews(string $locale): array
    {
        return News::query()
            ->with(['translations'])
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderByDesc('featured_until')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get()
            ->map(function (News $news) use ($locale): array {
                $translation = $news->translation($locale) ?? $news->translation('en');

                return [
                    'id' => $news->id,
                    'title' => $translation?->title,
                    'slug' => $translation?->slug,
                    'summary' => $translation?->summary,
                    'published_at' => $news->published_at?->toDateString(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id:int,title:?string,slug:?string,summary:?string,document_date:?string,file_type:?string}>
     */
    protected function latestDocuments(string $locale): array
    {
        return Document::query()
            ->with(['translations'])
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderByDesc('document_date')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get()
            ->map(function (Document $document) use ($locale): array {
                $translation = $document->translation($locale) ?? $document->translation('en');

                return [
                    'id' => $document->id,
                    'title' => $translation?->title,
                    'slug' => $translation?->slug,
                    'summary' => $translation?->summary,
                    'document_date' => $document->document_date?->toDateString(),
                    'file_type' => $document->file_type,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id:int,title:?string,slug:?string,summary:?string,status:string,closing_at:?string}>
     */
    protected function latestProcurements(string $locale): array
    {
        return Procurement::query()
            ->with(['translations'])
            ->whereIn('status', ['open', 'closed', 'awarded'])
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderByDesc('published_at')
            ->orderByDesc('closing_at')
            ->limit(3)
            ->get()
            ->map(function (Procurement $procurement) use ($locale): array {
                $translation = $procurement->translation($locale) ?? $procurement->translation('en');

                return [
                    'id' => $procurement->id,
                    'title' => $translation?->title,
                    'slug' => $translation?->slug,
                    'summary' => $translation?->summary,
                    'status' => $procurement->status,
                    'closing_at' => $procurement->closing_at?->toDateString(),
                ];
            })
            ->values()
            ->all();
    }

    protected function homeSeo(string $locale, ?Page $page): array
    {
        $translation = $page?->translation($locale) ?? $page?->translation('en');

        return [
            'title' => $translation?->seo_title ?? $translation?->title ?? config('app.name'),
            'description' => $translation?->seo_description ?? $translation?->summary ?? Setting::for('site', 'tagline'),
            'canonical_url' => route('public.home', ['locale' => $locale]),
            'type' => 'website',
        ];
    }

    protected function pageSeo(string $locale, Page $page): array
    {
        $translation = $page->translation($locale) ?? $page->translation('en');

        return [
            'title' => $translation?->seo_title ?? $translation?->title ?? config('app.name'),
            'description' => $translation?->seo_description ?? $translation?->summary ?? Setting::for('site', 'tagline'),
            'canonical_url' => route('public.pages.show', [
                'locale' => $locale,
                'slug' => $translation?->slug ?? '',
            ]),
            'type' => 'article',
            'image_url' => $page->getFirstMediaUrl('cover') ?: null,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function homeStructuredData(string $locale, ?Page $page): array
    {
        $translation = $page?->translation($locale) ?? $page?->translation('en');
        $homeUrl = route('public.home', ['locale' => $locale]);

        return [
            [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => Setting::for('site', 'name', config('app.name')),
                'url' => $homeUrl,
                'email' => Setting::for('contact', 'email'),
                'telephone' => Setting::for('contact', 'phone'),
                'address' => Setting::for('contact', 'address'),
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => Setting::for('site', 'name', config('app.name')),
                'url' => $homeUrl,
                'inLanguage' => $locale,
                'description' => $translation?->summary ?? Setting::for('site', 'tagline'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function webPageSchema(string $url, string $title, ?string $description): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'url' => $url,
            'name' => $title,
            'description' => $description,
        ];
    }
}

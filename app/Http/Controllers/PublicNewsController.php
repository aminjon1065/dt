<?php

namespace App\Http\Controllers;

use App\Http\Requests\Public\FilterPublicNewsRequest;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Setting;
use Inertia\Inertia;
use Inertia\Response;

class PublicNewsController extends Controller
{
    public function index(FilterPublicNewsRequest $request, string $locale): Response
    {
        $filters = $request->validated();

        $newsItems = News::query()
            ->with(['translations', 'categories.translations'])
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });

        if (($search = $filters['search'] ?? null) !== null) {
            $newsItems->whereHas('translations', function ($query) use ($locale, $search): void {
                $query->whereIn('locale', [$locale, 'en'])
                    ->where(function ($translationQuery) use ($search): void {
                        $translationQuery->where('title', 'like', "%{$search}%")
                            ->orWhere('summary', 'like', "%{$search}%")
                            ->orWhere('content', 'like', "%{$search}%");
                    });
            });
        }

        if (($category = $filters['category'] ?? null) !== null) {
            $newsItems->whereHas('categories', function ($query) use ($category): void {
                $query->where('slug', $category);
            });
        }

        $newsItems = $newsItems
            ->orderByDesc('featured_until')
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString()
            ->through(fn (News $news): array => $this->transformNewsListItem($news, $locale));

        return Inertia::render('public/news/index', [
            'locale' => $locale,
            'site' => $this->siteData($locale),
            'navigation' => app(PublicPageController::class)->navigation($locale),
            'indexUrl' => route('public.news.index', ['locale' => $locale]),
            'seo' => [
                'title' => 'News',
                'description' => 'Latest public updates, announcements, and project news.',
                'canonical_url' => route('public.news.index', ['locale' => $locale]),
                'type' => 'website',
            ],
            'filters' => [
                'search' => $filters['search'] ?? null,
                'category' => $filters['category'] ?? null,
            ],
            'categories' => NewsCategory::query()
                ->with('translations')
                ->where('is_active', true)
                ->orderBy('slug')
                ->get()
                ->map(fn (NewsCategory $category): array => [
                    'value' => $category->slug,
                    'label' => $category->translation($locale)?->name
                        ?? $category->translation('en')?->name
                        ?? $category->slug,
                ])
                ->values()
                ->all(),
            'newsItems' => $newsItems,
        ]);
    }

    public function show(string $locale, string $slug): Response
    {
        $news = News::query()
            ->with(['translations', 'categories.translations'])
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->whereHas('translations', function ($query) use ($locale, $slug): void {
                $query->where('locale', $locale)->where('slug', $slug);
            })
            ->firstOrFail();

        return Inertia::render('public/news/show', [
            'locale' => $locale,
            'site' => $this->siteData($locale),
            'navigation' => app(PublicPageController::class)->navigation($locale),
            'seo' => [
                'title' => $this->transformNews($news, $locale)['seo_title'] ?? $this->transformNews($news, $locale)['title'],
                'description' => $this->transformNews($news, $locale)['seo_description'] ?? $this->transformNews($news, $locale)['summary'],
                'canonical_url' => route('public.news.show', ['locale' => $locale, 'slug' => $slug]),
                'type' => 'article',
                'image_url' => $news->getFirstMediaUrl('cover') ?: null,
            ],
            'structuredData' => [[
                '@context' => 'https://schema.org',
                '@type' => 'NewsArticle',
                'headline' => $this->transformNews($news, $locale)['title'],
                'description' => $this->transformNews($news, $locale)['summary'],
                'datePublished' => $news->published_at?->toIso8601String(),
                'url' => route('public.news.show', ['locale' => $locale, 'slug' => $slug]),
            ]],
            'newsItem' => $this->transformNews($news, $locale),
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

    protected function transformNewsListItem(News $news, string $locale): array
    {
        $translation = $news->translation($locale) ?? $news->translation('en');

        return [
            'id' => $news->id,
            'title' => $translation?->title,
            'slug' => $translation?->slug,
            'summary' => $translation?->summary,
            'published_at' => $news->published_at?->toDateString(),
            'cover_url' => $news->getFirstMediaUrl('cover') ?: null,
            'categories' => $news->categories->map(
                fn ($category): string => $category->translation($locale)?->name
                    ?? $category->translation('en')?->name
                    ?? $category->slug
            )->values()->all(),
        ];
    }

    protected function transformNews(News $news, string $locale): array
    {
        $translation = $news->translation($locale) ?? $news->translation('en');

        abort_unless($translation !== null, 404);

        return [
            'id' => $news->id,
            'title' => $translation->title,
            'slug' => $translation->slug,
            'summary' => $translation->summary,
            'content' => $translation->content,
            'seo_title' => $translation->seo_title,
            'seo_description' => $translation->seo_description,
            'published_at' => $news->published_at?->toDateString(),
            'cover_url' => $news->getFirstMediaUrl('cover') ?: null,
            'categories' => $news->categories->map(
                fn ($category): string => $category->translation($locale)?->name
                    ?? $category->translation('en')?->name
                    ?? $category->slug
            )->values()->all(),
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Setting;
use Inertia\Inertia;
use Inertia\Response;

class PublicDocumentController extends Controller
{
    public function index(string $locale): Response
    {
        $documents = Document::query()
            ->with(['category.translations', 'tags.translations', 'translations'])
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderByDesc('document_date')
            ->orderByDesc('published_at')
            ->get()
            ->map(fn (Document $document): array => $this->transformDocumentListItem($document, $locale))
            ->values()
            ->all();

        return Inertia::render('public/documents/index', [
            'locale' => $locale,
            'site' => $this->siteData($locale),
            'navigation' => app(PublicPageController::class)->navigation($locale),
            'seo' => [
                'title' => 'Documents',
                'description' => 'Public document archive with downloadable files and official publications.',
                'canonical_url' => route('public.documents.index', ['locale' => $locale]),
                'type' => 'website',
            ],
            'documents' => $documents,
        ]);
    }

    public function show(string $locale, string $slug): Response
    {
        $document = Document::query()
            ->with(['category.translations', 'tags.translations', 'translations'])
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->whereHas('translations', function ($query) use ($locale, $slug): void {
                $query->where('locale', $locale)->where('slug', $slug);
            })
            ->firstOrFail();

        return Inertia::render('public/documents/show', [
            'locale' => $locale,
            'site' => $this->siteData($locale),
            'navigation' => app(PublicPageController::class)->navigation($locale),
            'seo' => [
                'title' => $this->transformDocument($document, $locale)['title'],
                'description' => $this->transformDocument($document, $locale)['summary'],
                'canonical_url' => route('public.documents.show', ['locale' => $locale, 'slug' => $slug]),
                'type' => 'article',
            ],
            'structuredData' => [[
                '@context' => 'https://schema.org',
                '@type' => 'DigitalDocument',
                'name' => $this->transformDocument($document, $locale)['title'],
                'description' => $this->transformDocument($document, $locale)['summary'],
                'datePublished' => $document->published_at?->toIso8601String(),
                'url' => route('public.documents.show', ['locale' => $locale, 'slug' => $slug]),
            ]],
            'document' => $this->transformDocument($document, $locale),
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

    protected function transformDocumentListItem(Document $document, string $locale): array
    {
        $translation = $document->translation($locale) ?? $document->translation('en');

        return [
            'id' => $document->id,
            'title' => $translation?->title,
            'slug' => $translation?->slug,
            'summary' => $translation?->summary,
            'file_type' => $document->file_type,
            'document_date' => $document->document_date?->toDateString(),
            'category' => $document->category->translation($locale)?->name
                ?? $document->category->translation('en')?->name
                ?? $document->category->slug,
            'tags' => $document->tags->map(
                fn ($tag): string => $tag->translation($locale)?->name
                    ?? $tag->translation('en')?->name
                    ?? $tag->slug
            )->values()->all(),
            'file_url' => $document->getFirstMediaUrl('documents') ?: null,
        ];
    }

    protected function transformDocument(Document $document, string $locale): array
    {
        $translation = $document->translation($locale) ?? $document->translation('en');

        abort_unless($translation !== null, 404);

        return [
            'id' => $document->id,
            'title' => $translation->title,
            'slug' => $translation->slug,
            'summary' => $translation->summary,
            'file_type' => $document->file_type,
            'document_date' => $document->document_date?->toDateString(),
            'category' => $document->category->translation($locale)?->name
                ?? $document->category->translation('en')?->name
                ?? $document->category->slug,
            'tags' => $document->tags->map(
                fn ($tag): string => $tag->translation($locale)?->name
                    ?? $tag->translation('en')?->name
                    ?? $tag->slug
            )->values()->all(),
            'file_url' => $document->getFirstMediaUrl('documents') ?: null,
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\News;
use App\Models\Page;
use App\Models\Procurement;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect();

        foreach (['en', 'tj', 'ru'] as $locale) {
            $urls->push([
                'loc' => route('public.home', ['locale' => $locale]),
                'lastmod' => now()->toDateString(),
            ]);

            $urls->push([
                'loc' => route('public.grm.create', ['locale' => $locale]),
                'lastmod' => now()->toDateString(),
            ]);
        }

        Page::query()
            ->with('translations')
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->get()
            ->each(function (Page $page) use ($urls): void {
                foreach ($page->translations as $translation) {
                    if (! filled($translation->slug)) {
                        continue;
                    }

                    $urls->push([
                        'loc' => route('public.pages.show', [
                            'locale' => $translation->locale,
                            'slug' => $translation->slug,
                        ]),
                        'lastmod' => $page->updated_at?->toDateString(),
                    ]);
                }
            });

        News::query()
            ->with('translations')
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->get()
            ->each(function (News $news) use ($urls): void {
                foreach ($news->translations as $translation) {
                    $urls->push([
                        'loc' => route('public.news.show', [
                            'locale' => $translation->locale,
                            'slug' => $translation->slug,
                        ]),
                        'lastmod' => ($news->published_at ?? $news->updated_at)?->toDateString(),
                    ]);
                }
            });

        Document::query()
            ->with('translations')
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->get()
            ->each(function (Document $document) use ($urls): void {
                foreach ($document->translations as $translation) {
                    $urls->push([
                        'loc' => route('public.documents.show', [
                            'locale' => $translation->locale,
                            'slug' => $translation->slug,
                        ]),
                        'lastmod' => ($document->published_at ?? $document->updated_at)?->toDateString(),
                    ]);
                }
            });

        Procurement::query()
            ->with('translations')
            ->whereIn('status', ['open', 'closed', 'awarded'])
            ->where(function ($query): void {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->get()
            ->each(function (Procurement $procurement) use ($urls): void {
                foreach ($procurement->translations as $translation) {
                    $urls->push([
                        'loc' => route('public.procurements.show', [
                            'locale' => $translation->locale,
                            'slug' => $translation->slug,
                        ]),
                        'lastmod' => ($procurement->published_at ?? $procurement->updated_at)?->toDateString(),
                    ]);
                }
            });

        $xml = view('sitemap', [
            'urls' => $urls->unique('loc')->values(),
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}

import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { Search } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import PublicLayout from '@/layouts/public-layout';

type NavigationItem = {
    id: number;
    label: string;
    href: string;
    children: Array<{ id: number; label: string; href: string }>;
};

type SiteData = {
    name: string;
    tagline?: string | null;
    contact_email?: string | null;
    contact_phone?: string | null;
    contact_address?: string | null;
    default_locale: string;
    locale: string;
};

type SeoData = {
    title?: string | null;
    description?: string | null;
    canonical_url?: string | null;
    robots?: string | null;
    type?: string | null;
    image_url?: string | null;
};

type SearchResultItem = {
    id: number;
    title: string | null;
    summary?: string | null;
    href: string;
    meta: string;
};

type SearchResults = {
    pages: SearchResultItem[];
    news: SearchResultItem[];
    documents: SearchResultItem[];
    procurements: SearchResultItem[];
    staff: SearchResultItem[];
};

const sections: Array<{ key: keyof SearchResults; label: string }> = [
    { key: 'pages', label: 'Pages' },
    { key: 'news', label: 'News' },
    { key: 'documents', label: 'Documents' },
    { key: 'procurements', label: 'Procurements' },
    { key: 'staff', label: 'Staff' },
];

export default function PublicSearchIndex({
    site,
    navigation,
    seo,
    searchUrl,
    filters,
    results,
}: {
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
    searchUrl: string;
    filters: { q?: string | null };
    results: SearchResults;
}) {
    const form = useForm(`PublicSearch:${site.locale}`, {
        q: filters.q ?? '',
    });

    const totalResults = sections.reduce(
        (total, section) => total + results[section.key].length,
        0,
    );

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.transform((data) => ({
            q: data.q.trim(),
        }));

        form.get(searchUrl, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    return (
        <PublicLayout title="Search" site={site} navigation={navigation} seo={seo}>
            <section className="mx-auto max-w-6xl px-4 py-12 md:py-20">
                <div className="max-w-3xl space-y-4">
                    <p className="text-sm font-medium uppercase tracking-[0.24em] text-stone-500">
                        Search
                    </p>
                    <h1 className="text-4xl font-semibold tracking-tight text-stone-950 md:text-5xl">
                        Find content across the portal
                    </h1>
                    <p className="text-lg leading-8 text-stone-600">
                        Search published pages, news, documents, procurement notices,
                        and staff profiles from one place.
                    </p>
                </div>

                <form
                    onSubmit={submit}
                    className="mt-10 flex flex-col gap-4 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm md:flex-row"
                >
                    <div className="relative flex-1">
                        <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-stone-400" />
                        <label htmlFor="site-search" className="sr-only">
                            Search the portal
                        </label>
                        <Input
                            id="site-search"
                            value={form.data.q}
                            onChange={(event) => form.setData('q', event.target.value)}
                            placeholder="Search by title, summary, keyword, or reference"
                            className="pl-10"
                        />
                    </div>
                    <Button type="submit" disabled={form.processing}>
                        Search
                    </Button>
                </form>

                {!filters.q && (
                    <div className="mt-8 rounded-3xl border border-dashed border-stone-300 bg-stone-50 px-6 py-10 text-center text-stone-600">
                        Enter a search term to browse results across the site.
                    </div>
                )}

                {filters.q && (
                    <div className="mt-8">
                        <p className="text-sm uppercase tracking-[0.18em] text-stone-500">
                            Results for "{filters.q}"
                        </p>
                        <h2 className="mt-2 text-2xl font-semibold tracking-tight text-stone-950">
                            {totalResults} result{totalResults === 1 ? '' : 's'} found
                        </h2>
                    </div>
                )}

                {filters.q && totalResults === 0 && (
                    <div className="mt-8 rounded-3xl border border-dashed border-stone-300 bg-stone-50 px-6 py-10 text-center text-stone-600">
                        No results matched your search.
                    </div>
                )}

                <div className="mt-10 space-y-8">
                    {sections.map((section) =>
                        results[section.key].length > 0 ? (
                            <section
                                key={section.key}
                                className="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm"
                            >
                                <div className="flex items-center justify-between gap-4">
                                    <h3 className="text-2xl font-semibold tracking-tight text-stone-950">
                                        {section.label}
                                    </h3>
                                    <span className="text-sm text-stone-500">
                                        {results[section.key].length}
                                    </span>
                                </div>

                                <div className="mt-5 grid gap-4">
                                    {results[section.key].map((item) => (
                                        <Link
                                            key={`${section.key}-${item.id}`}
                                            href={item.href}
                                            className="rounded-2xl border border-stone-200 px-5 py-4 transition hover:border-stone-300 hover:bg-stone-50"
                                        >
                                            <p className="text-xs uppercase tracking-[0.18em] text-stone-500">
                                                {item.meta}
                                            </p>
                                            <h4 className="mt-2 text-lg font-semibold text-stone-950">
                                                {item.title}
                                            </h4>
                                            {item.summary && (
                                                <p className="mt-2 text-sm leading-7 text-stone-600">
                                                    {item.summary}
                                                </p>
                                            )}
                                        </Link>
                                    ))}
                                </div>
                            </section>
                        ) : null,
                    )}
                </div>
            </section>
        </PublicLayout>
    );
}

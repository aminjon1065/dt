import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import PublicPagination from '@/components/public-pagination';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import PublicLayout from '@/layouts/public-layout';

type SiteData = {
    name: string;
    tagline?: string | null;
    contact_email?: string | null;
    contact_phone?: string | null;
    contact_address?: string | null;
    default_locale: string;
    locale: string;
};

type NavigationItem = {
    id: number;
    label: string;
    href: string;
    children: Array<{ id: number; label: string; href: string }>;
};

type SeoData = {
    title?: string | null;
    description?: string | null;
    canonical_url?: string | null;
    robots?: string | null;
    type?: string | null;
    image_url?: string | null;
};

type NewsListItem = {
    id: number;
    title: string | null;
    slug: string | null;
    summary?: string | null;
    published_at?: string | null;
    cover_url?: string | null;
    categories: string[];
};

type FilterOption = {
    value: string;
    label: string;
};

type NewsFilters = {
    search: string;
    category: string;
};

type PaginatedNewsItems = {
    data: NewsListItem[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

export default function PublicNewsIndex({
    site,
    navigation,
    seo,
    indexUrl,
    filters,
    categories,
    newsItems,
}: {
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
    indexUrl: string;
    filters: Partial<NewsFilters>;
    categories: FilterOption[];
    newsItems: PaginatedNewsItems;
}) {
    const form = useForm(`PublicNewsFilters:${site.locale}`, {
        search: filters.search ?? '',
        category: filters.category ?? '',
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.transform((data) =>
            Object.fromEntries(
                Object.entries(data).filter(([, value]) => value !== ''),
            ),
        );

        form.get(indexUrl, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const reset = () => {
        form.setData({
            search: '',
            category: '',
        });

        form.transform((data) => data);

        form.get(indexUrl, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    return (
        <PublicLayout title="News" site={site} navigation={navigation} seo={seo}>
            <section className="mx-auto max-w-6xl px-4 py-12 md:py-20">
                <div className="max-w-3xl space-y-4">
                    <p className="text-sm font-medium uppercase tracking-[0.24em] text-stone-500">
                        Updates
                    </p>
                    <h1 className="text-4xl font-semibold tracking-tight text-stone-950 md:text-5xl">
                        News and announcements
                    </h1>
                    <p className="text-lg leading-8 text-stone-600">
                        Published updates, announcements, and project news.
                    </p>
                </div>

                <form
                    onSubmit={submit}
                    className="mt-10 grid gap-4 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm md:grid-cols-[2fr_1fr_auto]"
                >
                    <div className="space-y-2">
                        <label htmlFor="news-search" className="text-sm font-medium text-stone-700">Search</label>
                        <Input
                            id="news-search"
                            value={form.data.search}
                            onChange={(event) => form.setData('search', event.target.value)}
                            placeholder="Title, summary, or content"
                        />
                    </div>

                    <div className="space-y-2">
                        <label htmlFor="news-category" className="text-sm font-medium text-stone-700">Category</label>
                        <select
                            id="news-category"
                            value={form.data.category}
                            onChange={(event) => form.setData('category', event.target.value)}
                            className="h-9 w-full rounded-md border border-stone-300 bg-white px-3 text-sm text-stone-900 shadow-xs outline-none transition focus:border-stone-500"
                        >
                            <option value="">All categories</option>
                            {categories.map((category) => (
                                <option key={category.value} value={category.value}>
                                    {category.label}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="flex flex-wrap items-end gap-3">
                        <Button type="submit" disabled={form.processing}>
                            Apply filters
                        </Button>
                        <Button type="button" variant="outline" onClick={reset}>
                            Reset
                        </Button>
                    </div>
                </form>

                <div className="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    {newsItems.data.map((item) => (
                        <article
                            key={item.id}
                            className="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm"
                        >
                            {item.cover_url && (
                                <img
                                    src={item.cover_url}
                                    alt={item.title ?? 'News image'}
                                    loading="lazy"
                                    decoding="async"
                                    className="h-52 w-full object-cover"
                                />
                            )}
                            <div className="space-y-4 p-6">
                                <div className="flex flex-wrap gap-2 text-xs uppercase tracking-[0.18em] text-stone-500">
                                    {item.categories.map((category) => (
                                        <span key={category}>{category}</span>
                                    ))}
                                </div>
                                <h2 className="text-2xl font-semibold tracking-tight">
                                    {item.title}
                                </h2>
                                {item.summary && (
                                    <p className="text-sm leading-7 text-stone-600">
                                        {item.summary}
                                    </p>
                                )}
                                <div className="flex items-center justify-between text-sm text-stone-500">
                                    <span>{item.published_at ?? ''}</span>
                                    {item.slug && (
                                        <Link
                                            href={`/${site.locale}/news/${item.slug}`}
                                            className="font-medium text-stone-900 underline"
                                        >
                                            Read
                                        </Link>
                                    )}
                                </div>
                            </div>
                        </article>
                    ))}
                </div>

                {newsItems.data.length === 0 && (
                    <div className="mt-6 rounded-3xl border border-dashed border-stone-300 bg-stone-50 px-6 py-10 text-center text-stone-600">
                        No news items matched the selected filters.
                    </div>
                )}

                <PublicPagination pagination={newsItems} />
            </section>
        </PublicLayout>
    );
}

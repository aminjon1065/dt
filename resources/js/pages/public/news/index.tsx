import { Link } from '@inertiajs/react';
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

export default function PublicNewsIndex({
    site,
    navigation,
    seo,
    newsItems,
}: {
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
    newsItems: NewsListItem[];
}) {
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

                <div className="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    {newsItems.map((item) => (
                        <article
                            key={item.id}
                            className="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm"
                        >
                            {item.cover_url && (
                                <img
                                    src={item.cover_url}
                                    alt={item.title ?? 'News image'}
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
            </section>
        </PublicLayout>
    );
}

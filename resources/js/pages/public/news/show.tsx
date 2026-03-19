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

type NewsItem = {
    id: number;
    title: string;
    slug: string;
    summary?: string | null;
    content?: string | null;
    seo_title?: string | null;
    seo_description?: string | null;
    published_at?: string | null;
    cover_url?: string | null;
    categories: string[];
};

export default function PublicNewsShow({
    site,
    navigation,
    seo,
    structuredData,
    newsItem,
}: {
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
    structuredData?: Array<Record<string, unknown>>;
    newsItem: NewsItem;
}) {
    return (
        <PublicLayout
            title={newsItem.seo_title || newsItem.title}
            site={site}
            navigation={navigation}
            seo={seo}
            structuredData={structuredData}
        >
            <article className="mx-auto max-w-4xl px-4 py-12 md:py-20">
                <div className="space-y-4">
                    <div className="flex flex-wrap gap-2 text-xs uppercase tracking-[0.18em] text-stone-500">
                        {newsItem.categories.map((category) => (
                            <span key={category}>{category}</span>
                        ))}
                    </div>
                    <h1 className="text-4xl font-semibold tracking-tight text-stone-950 md:text-5xl">
                        {newsItem.title}
                    </h1>
                    <p className="text-sm text-stone-500">
                        {newsItem.published_at ?? ''}
                    </p>
                    {newsItem.summary && (
                        <p className="text-lg leading-8 text-stone-600">
                            {newsItem.summary}
                        </p>
                    )}
                </div>

                {newsItem.cover_url && (
                    <img
                        src={newsItem.cover_url}
                        alt={newsItem.title}
                        className="mt-8 h-auto w-full rounded-3xl border border-stone-200 object-cover shadow-sm"
                    />
                )}

                <div className="prose prose-stone mt-10 max-w-none">
                    {newsItem.content ? (
                        <div
                            dangerouslySetInnerHTML={{
                                __html: newsItem.content,
                            }}
                        />
                    ) : (
                        <p>No content published yet.</p>
                    )}
                </div>
            </article>
        </PublicLayout>
    );
}

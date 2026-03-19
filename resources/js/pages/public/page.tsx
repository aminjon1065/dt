import PublicLayout from '@/layouts/public-layout';

type PageData = {
    id: number;
    title: string;
    slug: string;
    summary?: string | null;
    content?: string | null;
    seo_title?: string | null;
    seo_description?: string | null;
    cover_url?: string | null;
    children: Array<{ id: number; title: string | null; slug: string | null }>;
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

export default function PublicPage({
    page,
    site,
    navigation,
    isHome,
    seo,
    structuredData,
}: {
    page: PageData | null;
    site: SiteData;
    navigation: NavigationItem[];
    isHome: boolean;
    seo?: SeoData;
    structuredData?: Array<Record<string, unknown>>;
}) {
    const title = page?.seo_title || page?.title || site.name;

    return (
        <PublicLayout
            title={title}
            site={site}
            navigation={navigation}
            seo={seo}
            structuredData={structuredData}
        >
            <section className="mx-auto max-w-6xl px-4 py-12 md:py-20">
                <div className="grid gap-10 md:grid-cols-[minmax(0,1fr)_18rem]">
                    <article className="space-y-6">
                        <div className="space-y-4">
                            <p className="text-sm font-medium uppercase tracking-[0.24em] text-stone-500">
                                {isHome ? 'Home' : 'Page'}
                            </p>
                            <h1 className="max-w-3xl text-4xl font-semibold tracking-tight text-stone-950 md:text-5xl">
                                {page?.title ?? site.name}
                            </h1>
                            {(page?.summary || site.tagline) && (
                                <p className="max-w-2xl text-lg leading-8 text-stone-600">
                                    {page?.summary ?? site.tagline}
                                </p>
                            )}
                        </div>

                        {page?.cover_url && (
                            <img
                                src={page.cover_url}
                                alt={page.title}
                                className="h-auto w-full rounded-3xl border border-stone-200 object-cover shadow-sm"
                            />
                        )}

                        <div className="prose prose-stone max-w-none">
                            {page?.content ? (
                                <div dangerouslySetInnerHTML={{ __html: page.content }} />
                            ) : (
                                <p>
                                    This public page is ready. Publish homepage
                                    content from the CMS to replace this
                                    placeholder.
                                </p>
                            )}
                        </div>
                    </article>

                    <aside className="space-y-4 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                        <h2 className="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500">
                            In This Section
                        </h2>

                        {page?.children && page.children.length > 0 ? (
                            <div className="space-y-3">
                                {page.children.map((child) => (
                                    <a
                                        key={child.id}
                                        href={`/${site.locale}/${child.slug}`}
                                        className="block rounded-xl border border-stone-200 px-4 py-3 text-sm font-medium text-stone-700 transition hover:border-stone-400 hover:text-stone-950"
                                    >
                                        {child.title}
                                    </a>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-stone-500">
                                No child pages published yet.
                            </p>
                        )}
                    </aside>
                </div>
            </section>
        </PublicLayout>
    );
}

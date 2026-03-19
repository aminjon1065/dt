import { Head, Link } from '@inertiajs/react';

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

export default function PublicLayout({
    title,
    site,
    navigation,
    seo,
    structuredData = [],
    children,
}: {
    title: string;
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
    structuredData?: Array<Record<string, unknown>>;
    children: React.ReactNode;
}) {
    const metaTitle = seo?.title || title;
    const metaDescription = seo?.description || site.tagline || undefined;
    const canonicalUrl = seo?.canonical_url || undefined;
    const robots = seo?.robots || 'index,follow';
    const ogType = seo?.type || 'website';

    return (
        <>
            <Head title={metaTitle}>
                {metaDescription && (
                    <meta
                        head-key="description"
                        name="description"
                        content={metaDescription}
                    />
                )}
                <meta head-key="robots" name="robots" content={robots} />
                {canonicalUrl && (
                    <link
                        head-key="canonical"
                        rel="canonical"
                        href={canonicalUrl}
                    />
                )}
                <meta head-key="og:title" property="og:title" content={metaTitle} />
                {metaDescription && (
                    <meta
                        head-key="og:description"
                        property="og:description"
                        content={metaDescription}
                    />
                )}
                <meta head-key="og:type" property="og:type" content={ogType} />
                {canonicalUrl && (
                    <meta
                        head-key="og:url"
                        property="og:url"
                        content={canonicalUrl}
                    />
                )}
                <meta
                    head-key="og:site_name"
                    property="og:site_name"
                    content={site.name}
                />
                {seo?.image_url && (
                    <meta
                        head-key="og:image"
                        property="og:image"
                        content={seo.image_url}
                    />
                )}
                <meta
                    head-key="twitter:card"
                    name="twitter:card"
                    content="summary_large_image"
                />
                <meta
                    head-key="twitter:title"
                    name="twitter:title"
                    content={metaTitle}
                />
                {metaDescription && (
                    <meta
                        head-key="twitter:description"
                        name="twitter:description"
                        content={metaDescription}
                    />
                )}
                {structuredData.map((item, index) => (
                    <script
                        key={`structured-data-${index}`}
                        head-key={`structured-data-${index}`}
                        type="application/ld+json"
                        dangerouslySetInnerHTML={{
                            __html: JSON.stringify(item),
                        }}
                    />
                ))}
            </Head>

            <div className="min-h-screen bg-stone-50 text-stone-900">
                <header className="border-b border-stone-200 bg-white/90 backdrop-blur">
                    <div className="mx-auto flex max-w-6xl items-center justify-between gap-6 px-4 py-5">
                        <div>
                            <Link
                                href={`/${site.locale}`}
                                className="text-xl font-semibold tracking-tight"
                            >
                                {site.name}
                            </Link>
                            {site.tagline && (
                                <p className="text-sm text-stone-500">
                                    {site.tagline}
                                </p>
                            )}
                        </div>

                        <nav className="hidden items-center gap-6 md:flex">
                            {navigation.map((item) => (
                                <div key={item.id} className="group relative">
                                    <Link
                                        href={item.href}
                                        className="text-sm font-medium text-stone-700 transition hover:text-stone-950"
                                    >
                                        {item.label}
                                    </Link>

                                    {item.children.length > 0 && (
                                        <div className="invisible absolute top-full left-0 mt-3 min-w-48 rounded-xl border border-stone-200 bg-white p-2 opacity-0 shadow-lg transition group-hover:visible group-hover:opacity-100">
                                            {item.children.map((child) => (
                                                <Link
                                                    key={child.id}
                                                    href={child.href}
                                                    className="block rounded-lg px-3 py-2 text-sm text-stone-700 hover:bg-stone-100"
                                                >
                                                    {child.label}
                                                </Link>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            ))}
                        </nav>
                    </div>
                </header>

                <main>{children}</main>

                <footer className="mt-16 border-t border-stone-200 bg-white">
                    <div className="mx-auto grid max-w-6xl gap-4 px-4 py-8 text-sm text-stone-600 md:grid-cols-3">
                        <div>{site.name}</div>
                        <div>{site.contact_address ?? ''}</div>
                        <div className="text-left md:text-right">
                            {site.contact_email ?? site.contact_phone ?? ''}
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}

import { Link } from '@inertiajs/react';
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

type NewsItem = {
    id: number;
    title: string | null;
    slug: string | null;
    summary: string | null;
    published_at: string | null;
};

type DocumentItem = {
    id: number;
    title: string | null;
    slug: string | null;
    summary: string | null;
    document_date: string | null;
    file_type: string | null;
};

type ProcurementItem = {
    id: number;
    title: string | null;
    slug: string | null;
    summary: string | null;
    status: string;
    closing_at: string | null;
};

export default function PublicHome({
    page,
    site,
    navigation,
    seo,
    structuredData,
    latestNews,
    latestDocuments,
    latestProcurements,
}: {
    page: PageData | null;
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
    structuredData?: Array<Record<string, unknown>>;
    latestNews: NewsItem[];
    latestDocuments: DocumentItem[];
    latestProcurements: ProcurementItem[];
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
            <section className="border-b border-stone-200 bg-[radial-gradient(circle_at_top_left,_rgba(245,158,11,0.15),_transparent_30%),linear-gradient(135deg,_#fafaf9,_#f5f5f4)]">
                <div className="mx-auto grid max-w-6xl gap-10 px-4 py-14 md:grid-cols-[minmax(0,1.2fr)_minmax(18rem,0.8fr)] md:py-20">
                    <div className="space-y-6">
                        <p className="text-sm font-medium uppercase tracking-[0.26em] text-amber-700">
                            Public information portal
                        </p>
                        <h1 className="max-w-4xl text-4xl font-semibold tracking-tight text-stone-950 md:text-6xl">
                            {page?.title ?? site.name}
                        </h1>
                        <p className="max-w-2xl text-lg leading-8 text-stone-600">
                            {page?.summary ??
                                site.tagline ??
                                'Access official updates, procurement notices, public documents, and grievance reporting in one place.'}
                        </p>
                        <div className="flex flex-col gap-3 sm:flex-row">
                            <Link
                                href={`/${site.locale}/news`}
                                className="rounded-full bg-stone-950 px-5 py-3 text-sm font-medium text-white transition hover:bg-stone-800"
                            >
                                Explore latest news
                            </Link>
                            <Link
                                href={`/${site.locale}/grm`}
                                className="rounded-full border border-stone-300 bg-white px-5 py-3 text-sm font-medium text-stone-900 transition hover:border-stone-500"
                            >
                                Submit grievance
                            </Link>
                        </div>
                    </div>

                    <div className="grid gap-4 rounded-[2rem] border border-stone-200 bg-white/80 p-6 shadow-sm backdrop-blur">
                        <div className="rounded-3xl bg-stone-950 p-5 text-white">
                            <p className="text-xs uppercase tracking-[0.24em] text-stone-300">
                                Quick access
                            </p>
                            <div className="mt-4 grid gap-3 text-sm">
                                <Link href={`/${site.locale}/documents`} className="rounded-2xl bg-white/10 px-4 py-3 transition hover:bg-white/15">
                                    Documents archive
                                </Link>
                                <Link href={`/${site.locale}/procurements`} className="rounded-2xl bg-white/10 px-4 py-3 transition hover:bg-white/15">
                                    Procurement notices
                                </Link>
                                <Link href={`/${site.locale}/grm`} className="rounded-2xl bg-white/10 px-4 py-3 transition hover:bg-white/15">
                                    GRM and feedback
                                </Link>
                            </div>
                        </div>

                        <div className="grid gap-3 rounded-3xl border border-stone-200 bg-stone-50 p-5">
                            <p className="text-xs uppercase tracking-[0.22em] text-stone-500">
                                Contact
                            </p>
                            <p className="text-sm text-stone-700">
                                {site.contact_address ?? 'Official contact details will appear here once configured.'}
                            </p>
                            <p className="text-sm text-stone-700">
                                {site.contact_email ?? site.contact_phone ?? 'Contact channels are being configured.'}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section className="mx-auto max-w-6xl px-4 py-12 md:py-16">
                <div className="grid gap-6 md:grid-cols-3">
                    <SectionCard
                        eyebrow="Updates"
                        title="Latest news"
                        href={`/${site.locale}/news`}
                        emptyLabel="No news published yet."
                        items={latestNews.map((item) => ({
                            id: item.id,
                            title: item.title,
                            href: item.slug ? `/${site.locale}/news/${item.slug}` : null,
                            meta: item.published_at,
                            summary: item.summary,
                        }))}
                    />
                    <SectionCard
                        eyebrow="Archive"
                        title="Recent documents"
                        href={`/${site.locale}/documents`}
                        emptyLabel="No documents published yet."
                        items={latestDocuments.map((item) => ({
                            id: item.id,
                            title: item.title,
                            href: item.slug ? `/${site.locale}/documents/${item.slug}` : null,
                            meta: [item.file_type, item.document_date].filter(Boolean).join(' • '),
                            summary: item.summary,
                        }))}
                    />
                    <SectionCard
                        eyebrow="Procurement"
                        title="Open and recent notices"
                        href={`/${site.locale}/procurements`}
                        emptyLabel="No procurement notices published yet."
                        items={latestProcurements.map((item) => ({
                            id: item.id,
                            title: item.title,
                            href: item.slug ? `/${site.locale}/procurements/${item.slug}` : null,
                            meta: [item.status, item.closing_at].filter(Boolean).join(' • '),
                            summary: item.summary,
                        }))}
                    />
                </div>
            </section>

            <section className="mx-auto max-w-6xl px-4 pb-12 md:pb-16">
                <div className="grid gap-8 rounded-[2rem] border border-stone-200 bg-white p-8 shadow-sm md:grid-cols-[minmax(0,1fr)_20rem]">
                    <div className="space-y-4">
                        <p className="text-sm font-medium uppercase tracking-[0.24em] text-stone-500">
                            Public service
                        </p>
                        <h2 className="text-3xl font-semibold tracking-tight text-stone-950">
                            Grievance redress and feedback
                        </h2>
                        <p className="max-w-2xl text-base leading-8 text-stone-600">
                            If you need to report a problem, submit a complaint,
                            or provide formal feedback, use the GRM form. Each
                            submission receives a reference number for follow-up.
                        </p>
                    </div>

                    <div className="flex flex-col justify-between gap-4 rounded-3xl bg-stone-100 p-6">
                        <div>
                            <p className="text-sm text-stone-600">
                                The process is designed to stay simple on both
                                desktop and mobile devices.
                            </p>
                        </div>
                        <Link
                            href={`/${site.locale}/grm`}
                            className="inline-flex items-center justify-center rounded-full bg-stone-950 px-5 py-3 text-sm font-medium text-white transition hover:bg-stone-800"
                        >
                            Open GRM form
                        </Link>
                    </div>
                </div>
            </section>

            {page?.content && (
                <section className="mx-auto max-w-6xl px-4 pb-16">
                    <div className="prose prose-stone max-w-none rounded-[2rem] border border-stone-200 bg-white p-8 shadow-sm">
                        <div dangerouslySetInnerHTML={{ __html: page.content }} />
                    </div>
                </section>
            )}
        </PublicLayout>
    );
}

function SectionCard({
    eyebrow,
    title,
    href,
    emptyLabel,
    items,
}: {
    eyebrow: string;
    title: string;
    href: string;
    emptyLabel: string;
    items: Array<{
        id: number;
        title: string | null;
        href: string | null;
        meta: string | null;
        summary: string | null;
    }>;
}) {
    return (
        <div className="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm">
            <div className="flex items-start justify-between gap-4">
                <div>
                    <p className="text-xs uppercase tracking-[0.22em] text-stone-500">
                        {eyebrow}
                    </p>
                    <h2 className="mt-3 text-2xl font-semibold tracking-tight text-stone-950">
                        {title}
                    </h2>
                </div>
                <Link href={href} className="text-sm font-medium text-stone-900 underline">
                    View all
                </Link>
            </div>

            <div className="mt-6 space-y-4">
                {items.length > 0 ? (
                    items.map((item) => (
                        <div key={item.id} className="rounded-2xl border border-stone-200 p-4">
                            <p className="text-xs uppercase tracking-[0.18em] text-stone-500">
                                {item.meta || 'Published'}
                            </p>
                            {item.href ? (
                                <Link href={item.href} className="mt-2 block text-lg font-semibold tracking-tight text-stone-950 underline-offset-4 hover:underline">
                                    {item.title}
                                </Link>
                            ) : (
                                <p className="mt-2 text-lg font-semibold tracking-tight text-stone-950">
                                    {item.title}
                                </p>
                            )}
                            {item.summary && (
                                <p className="mt-2 text-sm leading-7 text-stone-600">
                                    {item.summary}
                                </p>
                            )}
                        </div>
                    ))
                ) : (
                    <p className="rounded-2xl border border-dashed border-stone-300 px-4 py-6 text-sm text-stone-500">
                        {emptyLabel}
                    </p>
                )}
            </div>
        </div>
    );
}

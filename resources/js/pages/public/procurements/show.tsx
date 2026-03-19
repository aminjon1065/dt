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

type ProcurementData = {
    id: number;
    reference_number: string;
    procurement_type: string;
    status: string;
    published_at?: string | null;
    closing_at?: string | null;
    title: string;
    slug: string;
    summary?: string | null;
    content?: string | null;
    attachments: Array<{ id: number; name: string; url: string }>;
};

export default function PublicProcurementShow({
    site,
    navigation,
    seo,
    structuredData,
    procurement,
}: {
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
    structuredData?: Array<Record<string, unknown>>;
    procurement: ProcurementData;
}) {
    return (
        <PublicLayout
            title={procurement.title}
            site={site}
            navigation={navigation}
            seo={seo}
            structuredData={structuredData}
        >
            <article className="mx-auto max-w-4xl px-4 py-12 md:py-20">
                <div className="space-y-4">
                    <div className="flex flex-wrap gap-3 text-xs uppercase tracking-[0.18em] text-stone-500">
                        <span>{procurement.reference_number}</span>
                        <span>{procurement.procurement_type}</span>
                        <span>{procurement.status}</span>
                    </div>
                    <h1 className="text-4xl font-semibold tracking-tight text-stone-950 md:text-5xl">
                        {procurement.title}
                    </h1>
                    {procurement.summary && (
                        <p className="text-lg leading-8 text-stone-600">
                            {procurement.summary}
                        </p>
                    )}
                </div>

                <div className="mt-8 grid gap-4 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm md:grid-cols-2">
                    <div>
                        <div className="text-xs uppercase tracking-[0.18em] text-stone-500">
                            Published
                        </div>
                        <div className="mt-2 text-stone-900">
                            {procurement.published_at ?? '—'}
                        </div>
                    </div>
                    <div>
                        <div className="text-xs uppercase tracking-[0.18em] text-stone-500">
                            Closing
                        </div>
                        <div className="mt-2 text-stone-900">
                            {procurement.closing_at ?? '—'}
                        </div>
                    </div>
                </div>

                <div className="prose prose-stone mt-10 max-w-none">
                    {procurement.content ? (
                        <div
                            dangerouslySetInnerHTML={{
                                __html: procurement.content,
                            }}
                        />
                    ) : (
                        <p>No notice details published yet.</p>
                    )}
                </div>

                {procurement.attachments.length > 0 && (
                    <div className="mt-10 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-stone-950">
                            Attachments
                        </h2>
                        <div className="mt-4 space-y-3">
                            {procurement.attachments.map((attachment) => (
                                <a
                                    key={attachment.id}
                                    href={attachment.url}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="block rounded-xl border border-stone-200 px-4 py-3 text-sm font-medium text-stone-700 underline"
                                >
                                    {attachment.name}
                                </a>
                            ))}
                        </div>
                    </div>
                )}
            </article>
        </PublicLayout>
    );
}

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

type DocumentData = {
    id: number;
    title: string;
    slug: string;
    summary?: string | null;
    content?: string | null;
    seo_title?: string | null;
    seo_description?: string | null;
    file_type?: string | null;
    document_date?: string | null;
    category: string;
    tags: string[];
    file_url?: string | null;
};

export default function PublicDocumentShow({
    site,
    navigation,
    seo,
    structuredData,
    document,
}: {
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
    structuredData?: Array<Record<string, unknown>>;
    document: DocumentData;
}) {
    return (
        <PublicLayout
            title={document.seo_title || document.title}
            site={site}
            navigation={navigation}
            seo={seo}
            structuredData={structuredData}
        >
            <article className="mx-auto max-w-4xl px-4 py-12 md:py-20">
                <div className="space-y-4">
                    <p className="text-sm font-medium uppercase tracking-[0.24em] text-stone-500">
                        {document.category}
                    </p>
                    <h1 className="text-4xl font-semibold tracking-tight text-stone-950 md:text-5xl">
                        {document.title}
                    </h1>
                    {document.summary && (
                        <p className="text-lg leading-8 text-stone-600">
                            {document.summary}
                        </p>
                    )}
                </div>

                <div className="prose prose-stone mt-8 max-w-none">
                    {document.content ? (
                        <div
                            dangerouslySetInnerHTML={{
                                __html: document.content,
                            }}
                        />
                    ) : null}
                </div>

                <div className="mt-8 grid gap-4 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm md:grid-cols-2">
                    <div>
                        <div className="text-xs uppercase tracking-[0.18em] text-stone-500">
                            Date
                        </div>
                        <div className="mt-2 text-stone-900">
                            {document.document_date ?? '—'}
                        </div>
                    </div>
                    <div>
                        <div className="text-xs uppercase tracking-[0.18em] text-stone-500">
                            File type
                        </div>
                        <div className="mt-2 uppercase text-stone-900">
                            {document.file_type ?? '—'}
                        </div>
                    </div>
                    <div className="md:col-span-2">
                        <div className="text-xs uppercase tracking-[0.18em] text-stone-500">
                            Tags
                        </div>
                        <div className="mt-2 flex flex-wrap gap-2">
                            {document.tags.map((tag) => (
                                <span
                                    key={tag}
                                    className="rounded-full bg-stone-100 px-3 py-1 text-sm text-stone-700"
                                >
                                    {tag}
                                </span>
                            ))}
                        </div>
                    </div>
                    {document.file_url && (
                        <div className="md:col-span-2">
                            <a
                                href={document.file_url}
                                target="_blank"
                                rel="noreferrer"
                                className="inline-flex rounded-full bg-stone-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-stone-700"
                            >
                                Download document
                            </a>
                        </div>
                    )}
                </div>
            </article>
        </PublicLayout>
    );
}

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

type ProcurementListItem = {
    id: number;
    reference_number: string;
    procurement_type: string;
    status: string;
    published_at?: string | null;
    closing_at?: string | null;
    title: string | null;
    slug: string | null;
    summary?: string | null;
};

export default function PublicProcurementIndex({
    site,
    navigation,
    seo,
    procurements,
}: {
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
    procurements: ProcurementListItem[];
}) {
    return (
        <PublicLayout
            title="Procurements"
            site={site}
            navigation={navigation}
            seo={seo}
        >
            <section className="mx-auto max-w-6xl px-4 py-12 md:py-20">
                <div className="max-w-3xl space-y-4">
                    <p className="text-sm font-medium uppercase tracking-[0.24em] text-stone-500">
                        Procurement
                    </p>
                    <h1 className="text-4xl font-semibold tracking-tight text-stone-950 md:text-5xl">
                        Procurement notices
                    </h1>
                    <p className="text-lg leading-8 text-stone-600">
                        Open, closed, and awarded procurement notices.
                    </p>
                </div>

                <div className="mt-10 space-y-5">
                    {procurements.map((procurement) => (
                        <article
                            key={procurement.id}
                            className="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm"
                        >
                            <div className="flex flex-wrap gap-3 text-xs uppercase tracking-[0.18em] text-stone-500">
                                <span>{procurement.reference_number}</span>
                                <span>{procurement.procurement_type}</span>
                                <span>{procurement.status}</span>
                            </div>
                            <h2 className="mt-4 text-2xl font-semibold tracking-tight text-stone-950">
                                {procurement.title}
                            </h2>
                            {procurement.summary && (
                                <p className="mt-3 max-w-3xl text-sm leading-7 text-stone-600">
                                    {procurement.summary}
                                </p>
                            )}
                            <div className="mt-4 flex items-center justify-between text-sm text-stone-500">
                                <span>
                                    Closing: {procurement.closing_at ?? '—'}
                                </span>
                                {procurement.slug && (
                                    <Link
                                        href={`/${site.locale}/procurements/${procurement.slug}`}
                                        className="font-medium text-stone-900 underline"
                                    >
                                        View notice
                                    </Link>
                                )}
                            </div>
                        </article>
                    ))}
                </div>
            </section>
        </PublicLayout>
    );
}

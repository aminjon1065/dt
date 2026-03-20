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

type FilterOption = {
    value: string;
    label: string;
};

type ProcurementFilters = {
    search: string;
    status: string;
    procurement_type: string;
};

type PaginatedProcurements = {
    data: ProcurementListItem[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

export default function PublicProcurementIndex({
    site,
    navigation,
    seo,
    indexUrl,
    filters,
    statuses,
    procurementTypes,
    procurements,
}: {
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
    indexUrl: string;
    filters: Partial<ProcurementFilters>;
    statuses: FilterOption[];
    procurementTypes: string[];
    procurements: PaginatedProcurements;
}) {
    const form = useForm(`PublicProcurementsFilters:${site.locale}`, {
        search: filters.search ?? '',
        status: filters.status ?? '',
        procurement_type: filters.procurement_type ?? '',
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
            status: '',
            procurement_type: '',
        });

        form.transform((data) => data);

        form.get(indexUrl, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

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

                <form
                    onSubmit={submit}
                    className="mt-10 grid gap-4 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm md:grid-cols-2 xl:grid-cols-4"
                >
                    <div className="space-y-2 xl:col-span-2">
                        <label htmlFor="procurements-search" className="text-sm font-medium text-stone-700">Search</label>
                        <Input
                            id="procurements-search"
                            value={form.data.search}
                            onChange={(event) => form.setData('search', event.target.value)}
                            placeholder="Reference, title, or summary"
                        />
                    </div>

                    <div className="space-y-2">
                        <label htmlFor="procurements-status" className="text-sm font-medium text-stone-700">Status</label>
                        <select
                            id="procurements-status"
                            value={form.data.status}
                            onChange={(event) => form.setData('status', event.target.value)}
                            className="h-9 w-full rounded-md border border-stone-300 bg-white px-3 text-sm text-stone-900 shadow-xs outline-none transition focus:border-stone-500"
                        >
                            <option value="">All statuses</option>
                            {statuses.map((status) => (
                                <option key={status.value} value={status.value}>
                                    {status.label}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="space-y-2">
                        <label htmlFor="procurements-type" className="text-sm font-medium text-stone-700">Type</label>
                        <select
                            id="procurements-type"
                            value={form.data.procurement_type}
                            onChange={(event) => form.setData('procurement_type', event.target.value)}
                            className="h-9 w-full rounded-md border border-stone-300 bg-white px-3 text-sm text-stone-900 shadow-xs outline-none transition focus:border-stone-500"
                        >
                            <option value="">All types</option>
                            {procurementTypes.map((procurementType) => (
                                <option key={procurementType} value={procurementType}>
                                    {procurementType}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="xl:col-span-4 flex flex-wrap gap-3">
                        <Button type="submit" disabled={form.processing}>
                            Apply filters
                        </Button>
                        <Button type="button" variant="outline" onClick={reset}>
                            Reset
                        </Button>
                    </div>
                </form>

                <div className="mt-10 space-y-5">
                    {procurements.data.map((procurement) => (
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

                {procurements.data.length === 0 && (
                    <div className="mt-6 rounded-3xl border border-dashed border-stone-300 bg-stone-50 px-6 py-10 text-center text-stone-600">
                        No procurement notices matched the selected filters.
                    </div>
                )}

                <PublicPagination pagination={procurements} />
            </section>
        </PublicLayout>
    );
}

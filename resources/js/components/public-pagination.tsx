import { Link } from '@inertiajs/react';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginationData = {
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLink[];
};

export default function PublicPagination({
    pagination,
}: {
    pagination: PaginationData;
}) {
    if (pagination.last_page <= 1) {
        return null;
    }

    return (
        <div className="mt-10 flex flex-col gap-4 rounded-3xl border border-stone-200 bg-white px-5 py-4 shadow-sm md:flex-row md:items-center md:justify-between">
            <p className="text-sm text-stone-600">
                Showing {pagination.from ?? 0}-{pagination.to ?? 0} of {pagination.total}
            </p>

            <nav aria-label="Pagination" className="flex flex-wrap items-center gap-2">
                {pagination.links.map((link, index) =>
                    link.url ? (
                        <Link
                            key={`${link.label}-${index}`}
                            href={link.url}
                            preserveScroll
                            preserveState
                            aria-current={link.active ? 'page' : undefined}
                            className={`rounded-full px-3 py-2 text-sm transition ${
                                link.active
                                    ? 'bg-stone-900 text-white'
                                    : 'border border-stone-200 bg-white text-stone-700 hover:border-stone-300 hover:bg-stone-50'
                            }`}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    ) : (
                        <span
                            key={`${link.label}-${index}`}
                            aria-hidden="true"
                            className="rounded-full border border-stone-200 px-3 py-2 text-sm text-stone-400"
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    ),
                )}
            </nav>
        </div>
    );
}

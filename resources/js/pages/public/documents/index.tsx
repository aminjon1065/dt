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

type DocumentListItem = {
    id: number;
    title: string | null;
    slug: string | null;
    summary?: string | null;
    file_type?: string | null;
    document_date?: string | null;
    category: string;
    tags: string[];
    file_url?: string | null;
};

type FilterOption = {
    value: string;
    label: string;
};

type DocumentFilters = {
    search: string;
    category: string;
    tag: string;
    file_type: string;
};

type PaginatedDocuments = {
    data: DocumentListItem[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

export default function PublicDocumentIndex({
    site,
    navigation,
    seo,
    indexUrl,
    filters,
    categories,
    tags,
    fileTypes,
    documents,
}: {
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
    indexUrl: string;
    filters: Partial<DocumentFilters>;
    categories: FilterOption[];
    tags: FilterOption[];
    fileTypes: string[];
    documents: PaginatedDocuments;
}) {
    const form = useForm(`PublicDocumentsFilters:${site.locale}`, {
        search: filters.search ?? '',
        category: filters.category ?? '',
        tag: filters.tag ?? '',
        file_type: filters.file_type ?? '',
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
            category: '',
            tag: '',
            file_type: '',
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
            title="Documents"
            site={site}
            navigation={navigation}
            seo={seo}
        >
            <section className="mx-auto max-w-6xl px-4 py-12 md:py-20">
                <div className="max-w-3xl space-y-4">
                    <p className="text-sm font-medium uppercase tracking-[0.24em] text-stone-500">
                        Archive
                    </p>
                    <h1 className="text-4xl font-semibold tracking-tight text-stone-950 md:text-5xl">
                        Documents archive
                    </h1>
                    <p className="text-lg leading-8 text-stone-600">
                        Public downloadable documents, reports, and policies.
                    </p>
                </div>

                <form
                    onSubmit={submit}
                    className="mt-10 grid gap-4 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm md:grid-cols-2 xl:grid-cols-5"
                >
                    <div className="space-y-2 xl:col-span-2">
                        <label htmlFor="documents-search" className="text-sm font-medium text-stone-700">Search</label>
                        <Input
                            id="documents-search"
                            value={form.data.search}
                            onChange={(event) => form.setData('search', event.target.value)}
                            placeholder="Title, summary, or keyword"
                        />
                    </div>

                    <div className="space-y-2">
                        <label htmlFor="documents-category" className="text-sm font-medium text-stone-700">Category</label>
                        <select
                            id="documents-category"
                            value={form.data.category}
                            onChange={(event) => form.setData('category', event.target.value)}
                            className="h-9 w-full rounded-md border border-stone-300 bg-white px-3 text-sm text-stone-900 shadow-xs outline-none transition focus:border-stone-500"
                        >
                            <option value="">All categories</option>
                            {categories.map((category) => (
                                <option key={category.value} value={category.value}>
                                    {category.label}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="space-y-2">
                        <label htmlFor="documents-tag" className="text-sm font-medium text-stone-700">Tag</label>
                        <select
                            id="documents-tag"
                            value={form.data.tag}
                            onChange={(event) => form.setData('tag', event.target.value)}
                            className="h-9 w-full rounded-md border border-stone-300 bg-white px-3 text-sm text-stone-900 shadow-xs outline-none transition focus:border-stone-500"
                        >
                            <option value="">All tags</option>
                            {tags.map((tag) => (
                                <option key={tag.value} value={tag.value}>
                                    {tag.label}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="space-y-2">
                        <label htmlFor="documents-file-type" className="text-sm font-medium text-stone-700">File type</label>
                        <select
                            id="documents-file-type"
                            value={form.data.file_type}
                            onChange={(event) => form.setData('file_type', event.target.value)}
                            className="h-9 w-full rounded-md border border-stone-300 bg-white px-3 text-sm text-stone-900 shadow-xs outline-none transition focus:border-stone-500"
                        >
                            <option value="">All file types</option>
                            {fileTypes.map((fileType) => (
                                <option key={fileType} value={fileType}>
                                    {fileType.toUpperCase()}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="xl:col-span-5 flex flex-wrap gap-3">
                        <Button type="submit" disabled={form.processing}>
                            Apply filters
                        </Button>
                        <Button type="button" variant="outline" onClick={reset}>
                            Reset
                        </Button>
                    </div>
                </form>

                <div className="mt-10 grid gap-4 md:hidden">
                    {documents.data.map((document) => (
                        <article
                            key={document.id}
                            className="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm"
                        >
                            <p className="text-xs uppercase tracking-[0.18em] text-stone-500">
                                {document.category}
                            </p>
                            <h2 className="mt-3 text-xl font-semibold tracking-tight text-stone-950">
                                {document.title}
                            </h2>
                            {document.summary && (
                                <p className="mt-2 text-sm leading-7 text-stone-600">
                                    {document.summary}
                                </p>
                            )}
                            <div className="mt-4 flex flex-wrap gap-3 text-sm text-stone-500">
                                <span>{document.document_date ?? '—'}</span>
                                <span className="uppercase">{document.file_type ?? '—'}</span>
                            </div>
                            <div className="mt-4 flex flex-wrap gap-4">
                                {document.slug && (
                                    <Link
                                        href={`/${site.locale}/documents/${document.slug}`}
                                        className="font-medium text-stone-900 underline"
                                    >
                                        View
                                    </Link>
                                )}
                                {document.file_url && (
                                    <a
                                        href={document.file_url}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="font-medium text-stone-900 underline"
                                    >
                                        Download
                                    </a>
                                )}
                            </div>
                        </article>
                    ))}
                </div>

                <div className="mt-10 hidden overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm md:block">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-stone-100/80">
                            <tr>
                                <th className="px-4 py-3 font-medium">Title</th>
                                <th className="px-4 py-3 font-medium">Category</th>
                                <th className="px-4 py-3 font-medium">Date</th>
                                <th className="px-4 py-3 font-medium">Type</th>
                                <th className="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {documents.data.map((document) => (
                                <tr key={document.id} className="border-t border-stone-200">
                                    <td className="px-4 py-4">
                                        <div className="font-medium text-stone-900">
                                            {document.title}
                                        </div>
                                        {document.summary && (
                                            <div className="mt-1 text-sm text-stone-600">
                                                {document.summary}
                                            </div>
                                        )}
                                    </td>
                                    <td className="px-4 py-4">{document.category}</td>
                                    <td className="px-4 py-4">{document.document_date ?? '—'}</td>
                                    <td className="px-4 py-4 uppercase">{document.file_type ?? '—'}</td>
                                    <td className="px-4 py-4">
                                        <div className="flex gap-3">
                                            {document.slug && (
                                                <Link
                                                    href={`/${site.locale}/documents/${document.slug}`}
                                                    className="underline"
                                                >
                                                    View
                                                </Link>
                                            )}
                                            {document.file_url && (
                                                <a
                                                    href={document.file_url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="underline"
                                                >
                                                    Download
                                                </a>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {documents.data.length === 0 && (
                    <div className="mt-6 rounded-3xl border border-dashed border-stone-300 bg-stone-50 px-6 py-10 text-center text-stone-600">
                        No documents matched the selected filters.
                    </div>
                )}

                <PublicPagination pagination={documents} />
            </section>
        </PublicLayout>
    );
}

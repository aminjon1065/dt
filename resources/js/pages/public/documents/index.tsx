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

export default function PublicDocumentIndex({
    site,
    navigation,
    seo,
    documents,
}: {
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
    documents: DocumentListItem[];
}) {
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

                <div className="mt-10 overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
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
                            {documents.map((document) => (
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
            </section>
        </PublicLayout>
    );
}

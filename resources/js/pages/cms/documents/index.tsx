import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { create, edit, index } from '@/routes/cms/documents';
import type { BreadcrumbItem } from '@/types';

type DocumentListItem = {
    id: number;
    status: string;
    file_type: string | null;
    document_date: string | null;
    published_at: string | null;
    category: string;
    tags: string[];
    translations: Record<string, { title: string; slug: string }>;
    file_url: string | null;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Documents',
        href: index(),
    },
];

export default function DocumentIndex({
    documents,
    status,
}: {
    documents: DocumentListItem[];
    status?: string;
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Documents" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Documents</h1>
                        <p className="text-sm text-muted-foreground">
                            Manage the public document archive, categories, and
                            downloadable files.
                        </p>
                    </div>

                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Create document
                        </Link>
                    </Button>
                </div>

                {status && (
                    <div className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {status}
                    </div>
                )}

                <div className="overflow-hidden rounded-xl border">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-muted/40">
                            <tr>
                                <th className="px-4 py-3 font-medium">Title</th>
                                <th className="px-4 py-3 font-medium">
                                    Category
                                </th>
                                <th className="px-4 py-3 font-medium">Tags</th>
                                <th className="px-4 py-3 font-medium">Status</th>
                                <th className="px-4 py-3 font-medium">File</th>
                                <th className="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {documents.map((document) => (
                                <tr key={document.id} className="border-t">
                                    <td className="px-4 py-3">
                                        <div className="font-medium">
                                            {document.translations.en?.title ??
                                                `Document #${document.id}`}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            /{document.translations.en?.slug ?? ''}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3">
                                        {document.category}
                                    </td>
                                    <td className="px-4 py-3">
                                        {document.tags.length > 0
                                            ? document.tags.join(', ')
                                            : '—'}
                                    </td>
                                    <td className="px-4 py-3 capitalize">
                                        {document.status}
                                    </td>
                                    <td className="px-4 py-3">
                                        {document.file_url ? (
                                            <a
                                                href={document.file_url}
                                                target="_blank"
                                                rel="noreferrer"
                                                className="underline"
                                            >
                                                Open
                                            </a>
                                        ) : (
                                            '—'
                                        )}
                                    </td>
                                    <td className="px-4 py-3">
                                        <Link
                                            href={edit(document.id)}
                                            className="text-sm underline"
                                        >
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                            ))}

                            {documents.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        No documents created yet.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}

import { Form, Head, Link } from '@inertiajs/react';
import { destroy, update } from '@/actions/App/Http/Controllers/Cms/DocumentController';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import DocumentForm from '@/pages/cms/documents/form';
import { edit, index } from '@/routes/cms/documents';
import type { BreadcrumbItem } from '@/types';

type SelectOption = {
    id: number;
    name: string;
};

type DocumentFormData = {
    id: number;
    document_category_id: number;
    status: string;
    file_type?: string | null;
    document_date: string | null;
    published_at: string | null;
    archived_at: string | null;
    tag_ids: number[];
    file_url?: string | null;
    translations: Record<
        'en' | 'tj' | 'ru',
        {
            title: string;
            slug: string;
            summary?: string | null;
        }
    >;
};

const breadcrumbs = (documentId: number): BreadcrumbItem[] => [
    {
        title: 'Documents',
        href: index(),
    },
    {
        title: 'Edit',
        href: edit(documentId),
    },
];

export default function EditDocument({
    document,
    categories,
    tags,
    status,
}: {
    document: DocumentFormData;
    categories: SelectOption[];
    tags: SelectOption[];
    status?: string;
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs(document.id)}>
            <Head title="Edit document" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Edit document</h1>
                        <p className="text-sm text-muted-foreground">
                            Update metadata, tags, and the downloadable file.
                        </p>
                    </div>

                    <Button variant="outline" asChild>
                        <Link href={index()}>Back to documents</Link>
                    </Button>
                </div>

                {status && (
                    <div className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {status}
                    </div>
                )}

                <DocumentForm
                    action={update.form(document.id)}
                    categories={categories}
                    tags={tags}
                    document={document}
                    submitLabel="Save changes"
                />

                <Form {...destroy.form(document.id)}>
                    {({ processing }) => (
                        <Button variant="destructive" disabled={processing}>
                            Delete document
                        </Button>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}

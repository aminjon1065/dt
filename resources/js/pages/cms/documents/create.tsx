import { Head } from '@inertiajs/react';
import { store } from '@/actions/App/Http/Controllers/Cms/DocumentController';
import AppLayout from '@/layouts/app-layout';
import DocumentForm from '@/pages/cms/documents/form';
import { create, index } from '@/routes/cms/documents';
import type { BreadcrumbItem } from '@/types';

type SelectOption = {
    id: number;
    name: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Documents',
        href: index(),
    },
    {
        title: 'Create',
        href: create(),
    },
];

export default function CreateDocument({
    categories,
    tags,
}: {
    categories: SelectOption[];
    tags: SelectOption[];
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create document" />

            <div className="space-y-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold">Create document</h1>
                    <p className="text-sm text-muted-foreground">
                        Add a downloadable file to the public archive.
                    </p>
                </div>

                <DocumentForm
                    action={store.form()}
                    categories={categories}
                    tags={tags}
                    submitLabel="Create document"
                />
            </div>
        </AppLayout>
    );
}

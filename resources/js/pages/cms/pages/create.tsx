import { Head } from '@inertiajs/react';
import { store } from '@/actions/App/Http/Controllers/Cms/PageController';
import AppLayout from '@/layouts/app-layout';
import PageForm from '@/pages/cms/pages/form';
import { create, index } from '@/routes/cms/pages';
import type { BreadcrumbItem } from '@/types';

type ParentPage = {
    id: number;
    title: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pages',
        href: index(),
    },
    {
        title: 'Create',
        href: create(),
    },
];

export default function CreatePage({
    parentPages,
    availableStatuses,
}: {
    parentPages: ParentPage[];
    availableStatuses: Array<{ value: string; label: string }>;
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create page" />

            <div className="space-y-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold">Create page</h1>
                    <p className="text-sm text-muted-foreground">
                        Add a new multilingual CMS page.
                    </p>
                </div>

                <PageForm
                    action={store.form()}
                    parentPages={parentPages}
                    availableStatuses={availableStatuses}
                    submitLabel="Create page"
                />
            </div>
        </AppLayout>
    );
}

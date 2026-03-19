import { Head } from '@inertiajs/react';
import { store } from '@/actions/App/Http/Controllers/Cms/ProcurementController';
import AppLayout from '@/layouts/app-layout';
import ProcurementForm from '@/pages/cms/procurements/form';
import { create, index } from '@/routes/cms/procurements';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Procurements',
        href: index(),
    },
    {
        title: 'Create',
        href: create(),
    },
];

export default function CreateProcurement() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create procurement" />

            <div className="space-y-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold">
                        Create procurement
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Add a multilingual procurement notice with publication
                        dates and attachments.
                    </p>
                </div>

                <ProcurementForm
                    action={store.form()}
                    submitLabel="Create procurement"
                />
            </div>
        </AppLayout>
    );
}

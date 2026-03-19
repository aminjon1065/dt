import { Head } from '@inertiajs/react';
import { store } from '@/actions/App/Http/Controllers/Cms/GrmSubmissionController';
import AppLayout from '@/layouts/app-layout';
import GrmSubmissionForm from '@/pages/cms/grm-submissions/form';
import { create, index } from '@/routes/cms/grm-submissions';
import type { BreadcrumbItem } from '@/types';

type UserOption = {
    id: number;
    name: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'GRM',
        href: index(),
    },
    {
        title: 'Create',
        href: create(),
    },
];

export default function CreateGrmSubmission({
    users,
}: {
    users: UserOption[];
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create GRM submission" />

            <div className="space-y-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold">
                        Create GRM submission
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Add a grievance or feedback case to the admin queue.
                    </p>
                </div>

                <GrmSubmissionForm
                    action={store.form()}
                    users={users}
                    submitLabel="Create submission"
                />
            </div>
        </AppLayout>
    );
}

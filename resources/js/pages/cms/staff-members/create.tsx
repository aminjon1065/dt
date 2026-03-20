import { Head } from '@inertiajs/react';
import { store } from '@/actions/App/Http/Controllers/Cms/StaffMemberController';
import AppLayout from '@/layouts/app-layout';
import StaffMemberForm from '@/pages/cms/staff-members/form';
import { create, index } from '@/routes/cms/staff-members';
import type { BreadcrumbItem } from '@/types';

type ParentStaffMember = {
    id: number;
    name: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Staff',
        href: index(),
    },
    {
        title: 'Create',
        href: create(),
    },
];

export default function CreateStaffMember({
    parentStaffMembers,
}: {
    parentStaffMembers: ParentStaffMember[];
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create staff member" />

            <div className="space-y-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold">Create staff member</h1>
                    <p className="text-sm text-muted-foreground">
                        Add a staff profile for the public directory.
                    </p>
                </div>

                <StaffMemberForm
                    action={store.form()}
                    parentStaffMembers={parentStaffMembers}
                    submitLabel="Create staff member"
                />
            </div>
        </AppLayout>
    );
}

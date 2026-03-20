import { Head } from '@inertiajs/react';
import { update } from '@/actions/App/Http/Controllers/Cms/StaffMemberController';
import AppLayout from '@/layouts/app-layout';
import StaffMemberForm from '@/pages/cms/staff-members/form';
import { edit, index } from '@/routes/cms/staff-members';
import type { BreadcrumbItem } from '@/types';

type TranslationFields = {
    name: string;
    slug: string;
    position?: string | null;
    bio?: string | null;
    seo_title?: string | null;
    seo_description?: string | null;
};

type StaffMemberData = {
    id: number;
    parent_id: number | null;
    email?: string | null;
    phone?: string | null;
    office_location?: string | null;
    show_email_publicly: boolean;
    show_phone_publicly: boolean;
    status: string;
    published_at: string | null;
    archived_at: string | null;
    sort_order: number;
    photo_url?: string | null;
    current_photo?: {
        id: number;
        name: string;
        url: string;
    } | null;
    translations: Record<'en' | 'tj' | 'ru', TranslationFields>;
};

type ParentStaffMember = {
    id: number;
    name: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Staff',
        href: index(),
    },
];

export default function EditStaffMember({
    staffMember,
    parentStaffMembers,
}: {
    staffMember: StaffMemberData;
    parentStaffMembers: ParentStaffMember[];
}) {
    return (
        <AppLayout
            breadcrumbs={[
                ...breadcrumbs,
                {
                    title: staffMember.translations.en?.name ?? `Staff Member #${staffMember.id}`,
                    href: edit(staffMember.id),
                },
            ]}
        >
            <Head title="Edit staff member" />

            <div className="space-y-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold">Edit staff member</h1>
                    <p className="text-sm text-muted-foreground">
                        Update staff profile details and publication settings.
                    </p>
                </div>

                <StaffMemberForm
                    action={update.form(staffMember.id)}
                    parentStaffMembers={parentStaffMembers}
                    staffMember={staffMember}
                    submitLabel="Save staff member"
                />
            </div>
        </AppLayout>
    );
}

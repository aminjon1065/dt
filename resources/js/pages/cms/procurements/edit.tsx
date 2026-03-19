import { Form, Head, Link } from '@inertiajs/react';
import { destroy, update } from '@/actions/App/Http/Controllers/Cms/ProcurementController';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import ProcurementForm from '@/pages/cms/procurements/form';
import { edit, index } from '@/routes/cms/procurements';
import type { BreadcrumbItem } from '@/types';

type ProcurementFormData = {
    id: number;
    reference_number: string;
    procurement_type: string;
    status: string;
    published_at: string | null;
    closing_at: string | null;
    archived_at: string | null;
    attachments: Array<{ id: number; name: string; url: string }>;
    translations: Record<
        'en' | 'tj' | 'ru',
        {
            title: string;
            slug: string;
            summary?: string | null;
            content?: string | null;
            seo_title?: string | null;
            seo_description?: string | null;
        }
    >;
};

const breadcrumbs = (procurementId: number): BreadcrumbItem[] => [
    {
        title: 'Procurements',
        href: index(),
    },
    {
        title: 'Edit',
        href: edit(procurementId),
    },
];

export default function EditProcurement({
    procurement,
    status,
}: {
    procurement: ProcurementFormData;
    status?: string;
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs(procurement.id)}>
            <Head title="Edit procurement" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">
                            Edit procurement
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Update the procurement lifecycle, localized content,
                            and attached files.
                        </p>
                    </div>

                    <Button variant="outline" asChild>
                        <Link href={index()}>Back to procurements</Link>
                    </Button>
                </div>

                {status && (
                    <div className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {status}
                    </div>
                )}

                <ProcurementForm
                    action={update.form(procurement.id)}
                    procurement={procurement}
                    submitLabel="Save changes"
                />

                <Form {...destroy.form(procurement.id)}>
                    {({ processing }) => (
                        <Button variant="destructive" disabled={processing}>
                            Delete procurement
                        </Button>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}

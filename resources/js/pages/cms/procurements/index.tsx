import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { create, edit, index } from '@/routes/cms/procurements';
import type { BreadcrumbItem } from '@/types';

type ProcurementListItem = {
    id: number;
    reference_number: string;
    procurement_type: string;
    status: string;
    published_at: string | null;
    closing_at: string | null;
    translations: Record<string, { title: string; slug: string }>;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Procurements',
        href: index(),
    },
];

export default function ProcurementIndex({
    procurements,
    status,
}: {
    procurements: ProcurementListItem[];
    status?: string;
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Procurements" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">
                            Procurements
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Manage multilingual procurement notices and their
                            publication lifecycle.
                        </p>
                    </div>

                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Create procurement
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
                                <th className="px-4 py-3 font-medium">
                                    Reference
                                </th>
                                <th className="px-4 py-3 font-medium">Title</th>
                                <th className="px-4 py-3 font-medium">Type</th>
                                <th className="px-4 py-3 font-medium">Status</th>
                                <th className="px-4 py-3 font-medium">
                                    Closing
                                </th>
                                <th className="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {procurements.map((procurement) => (
                                <tr key={procurement.id} className="border-t">
                                    <td className="px-4 py-3 font-medium">
                                        {procurement.reference_number}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="font-medium">
                                            {procurement.translations.en
                                                ?.title ??
                                                `Procurement #${procurement.id}`}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            /
                                            {procurement.translations.en
                                                ?.slug ?? ''}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 capitalize">
                                        {procurement.procurement_type}
                                    </td>
                                    <td className="px-4 py-3 capitalize">
                                        {procurement.status}
                                    </td>
                                    <td className="px-4 py-3">
                                        {procurement.closing_at ?? '—'}
                                    </td>
                                    <td className="px-4 py-3">
                                        <Link
                                            href={edit(procurement.id)}
                                            className="text-sm underline"
                                        >
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                            ))}

                            {procurements.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        No procurements created yet.
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

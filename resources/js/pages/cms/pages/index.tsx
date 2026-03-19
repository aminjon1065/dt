import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { create, edit, index } from '@/routes/cms/pages';
import type { BreadcrumbItem } from '@/types';

type PageListItem = {
    id: number;
    parent_id: number | null;
    parent_title?: string | null;
    template: string;
    status: string;
    published_at: string | null;
    archived_at: string | null;
    sort_order: number;
    is_home: boolean;
    translations: Record<string, { title: string; slug: string }>;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pages',
        href: index(),
    },
];

export default function PagesIndex({
    pages,
    status,
}: {
    pages: PageListItem[];
    status?: string;
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Pages" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Pages</h1>
                        <p className="text-sm text-muted-foreground">
                            Manage multilingual pages and their publication
                            lifecycle.
                        </p>
                    </div>

                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Create page
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
                                <th className="px-4 py-3 font-medium">Status</th>
                                <th className="px-4 py-3 font-medium">
                                    Template
                                </th>
                                <th className="px-4 py-3 font-medium">Parent</th>
                                <th className="px-4 py-3 font-medium">Order</th>
                                <th className="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {pages.map((page) => (
                                <tr key={page.id} className="border-t">
                                    <td className="px-4 py-3">
                                        <div className="font-medium">
                                            {page.translations.en?.title ??
                                                `Page #${page.id}`}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            /{page.translations.en?.slug ?? ''}
                                            {page.is_home && ' · Home'}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 capitalize">
                                        {page.status}
                                    </td>
                                    <td className="px-4 py-3">
                                        {page.template}
                                    </td>
                                    <td className="px-4 py-3">
                                        {page.parent_title ?? '—'}
                                    </td>
                                    <td className="px-4 py-3">
                                        {page.sort_order}
                                    </td>
                                    <td className="px-4 py-3">
                                        <Link
                                            href={edit(page.id)}
                                            className="text-sm underline"
                                        >
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                            ))}

                            {pages.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        No pages created yet.
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

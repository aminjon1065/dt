import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { create, edit, index } from '@/routes/cms/menus';
import type { BreadcrumbItem } from '@/types';

type MenuListItem = {
    id: number;
    name: string;
    slug: string;
    location?: string | null;
    items_count: number;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Menus',
        href: index(),
    },
];

export default function MenuIndex({
    menus,
    status,
}: {
    menus: MenuListItem[];
    status?: string;
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Menus" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Menus</h1>
                        <p className="text-sm text-muted-foreground">
                            Manage navigation groups and menu items.
                        </p>
                    </div>
                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Create menu
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
                                <th className="px-4 py-3 font-medium">Name</th>
                                <th className="px-4 py-3 font-medium">Slug</th>
                                <th className="px-4 py-3 font-medium">
                                    Location
                                </th>
                                <th className="px-4 py-3 font-medium">Items</th>
                                <th className="px-4 py-3 font-medium">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {menus.map((menu) => (
                                <tr key={menu.id} className="border-t">
                                    <td className="px-4 py-3 font-medium">
                                        {menu.name}
                                    </td>
                                    <td className="px-4 py-3">{menu.slug}</td>
                                    <td className="px-4 py-3">
                                        {menu.location ?? '—'}
                                    </td>
                                    <td className="px-4 py-3">
                                        {menu.items_count}
                                    </td>
                                    <td className="px-4 py-3">
                                        <Link
                                            href={edit(menu.id)}
                                            className="text-sm underline"
                                        >
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                            ))}

                            {menus.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={5}
                                        className="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        No menus created yet.
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

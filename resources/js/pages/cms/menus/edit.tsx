import { Form, Head, Link } from '@inertiajs/react';
import { destroy, update } from '@/actions/App/Http/Controllers/Cms/MenuController';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import MenuForm from '@/pages/cms/menus/form';
import { edit, index } from '@/routes/cms/menus';
import type { BreadcrumbItem } from '@/types';

type MenuItemData = {
    id?: number;
    item_key: string;
    parent_item_key?: string | null;
    label: string;
    locale?: string | null;
    url?: string | null;
    route_name?: string | null;
    sort_order: number;
    is_active: boolean;
};

type MenuFormData = {
    id: number;
    name: string;
    slug: string;
    location?: string | null;
    items: MenuItemData[];
};

const breadcrumbs = (menuId: number): BreadcrumbItem[] => [
    {
        title: 'Menus',
        href: index(),
    },
    {
        title: 'Edit',
        href: edit(menuId),
    },
];

export default function EditMenu({
    menu,
    status,
}: {
    menu: MenuFormData;
    status?: string;
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs(menu.id)}>
            <Head title="Edit menu" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Edit menu</h1>
                        <p className="text-sm text-muted-foreground">
                            Update menu metadata and navigation items.
                        </p>
                    </div>
                    <Button variant="outline" asChild>
                        <Link href={index()}>Back to menus</Link>
                    </Button>
                </div>

                {status && (
                    <div className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {status}
                    </div>
                )}

                <MenuForm
                    action={update.form(menu.id)}
                    menu={menu}
                    submitLabel="Save changes"
                />

                <Form {...destroy.form(menu.id)}>
                    {({ processing }) => (
                        <Button variant="destructive" disabled={processing}>
                            Delete menu
                        </Button>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}

import { Head } from '@inertiajs/react';
import { store } from '@/actions/App/Http/Controllers/Cms/MenuController';
import AppLayout from '@/layouts/app-layout';
import MenuForm from '@/pages/cms/menus/form';
import { create, index } from '@/routes/cms/menus';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Menus',
        href: index(),
    },
    {
        title: 'Create',
        href: create(),
    },
];

export default function CreateMenu() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create menu" />

            <div className="space-y-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold">Create menu</h1>
                    <p className="text-sm text-muted-foreground">
                        Create a navigation group and its items.
                    </p>
                </div>

                <MenuForm action={store.form()} submitLabel="Create menu" />
            </div>
        </AppLayout>
    );
}

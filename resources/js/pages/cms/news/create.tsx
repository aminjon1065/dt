import { Head } from '@inertiajs/react';
import { store } from '@/actions/App/Http/Controllers/Cms/NewsController';
import AppLayout from '@/layouts/app-layout';
import NewsForm from '@/pages/cms/news/form';
import { create, index } from '@/routes/cms/news';
import type { BreadcrumbItem } from '@/types';

type Category = {
    id: number;
    name: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'News',
        href: index(),
    },
    {
        title: 'Create',
        href: create(),
    },
];

export default function CreateNews({ categories }: { categories: Category[] }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create news" />

            <div className="space-y-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold">Create news</h1>
                    <p className="text-sm text-muted-foreground">
                        Add a multilingual update or announcement.
                    </p>
                </div>

                <NewsForm
                    action={store.form()}
                    categories={categories}
                    submitLabel="Create news"
                />
            </div>
        </AppLayout>
    );
}

import { Form, Head, Link } from '@inertiajs/react';
import {
    destroy,
    update,
} from '@/actions/App/Http/Controllers/Cms/PageController';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import PageForm from '@/pages/cms/pages/form';
import { edit, index } from '@/routes/cms/pages';
import type { BreadcrumbItem } from '@/types';

type ParentPage = {
    id: number;
    title: string;
};

type PageFormData = {
    id: number;
    parent_id: number | null;
    template: string;
    status: string;
    published_at: string | null;
    archived_at: string | null;
    sort_order: number;
    is_home: boolean;
    cover_url?: string | null;
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

const breadcrumbs = (pageId: number): BreadcrumbItem[] => [
    {
        title: 'Pages',
        href: index(),
    },
    {
        title: 'Edit',
        href: edit(pageId),
    },
];

export default function EditPage({
    page,
    parentPages,
    status,
}: {
    page: PageFormData;
    parentPages: ParentPage[];
    status?: string;
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs(page.id)}>
            <Head title="Edit page" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Edit page</h1>
                        <p className="text-sm text-muted-foreground">
                            Update content, metadata, hierarchy, and publication
                            state.
                        </p>
                    </div>

                    <Button variant="outline" asChild>
                        <Link href={index()}>Back to pages</Link>
                    </Button>
                </div>

                {status && (
                    <div className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {status}
                    </div>
                )}

                <PageForm
                    action={update.form(page.id)}
                    page={page}
                    parentPages={parentPages}
                    submitLabel="Save changes"
                />

                <Form {...destroy.form(page.id)}>
                    {({ processing }) => (
                        <Button variant="destructive" disabled={processing}>
                            Delete page
                        </Button>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}

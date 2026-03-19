import { Form, Head, Link } from '@inertiajs/react';
import {
    destroy,
    update,
} from '@/actions/App/Http/Controllers/Cms/NewsController';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import NewsForm from '@/pages/cms/news/form';
import { edit, index } from '@/routes/cms/news';
import type { BreadcrumbItem } from '@/types';

type Category = {
    id: number;
    name: string;
};

type NewsFormData = {
    id: number;
    status: string;
    published_at: string | null;
    archived_at: string | null;
    featured_until: string | null;
    category_ids: number[];
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

const breadcrumbs = (newsId: number): BreadcrumbItem[] => [
    {
        title: 'News',
        href: index(),
    },
    {
        title: 'Edit',
        href: edit(newsId),
    },
];

export default function EditNews({
    newsItem,
    categories,
    status,
}: {
    newsItem: NewsFormData;
    categories: Category[];
    status?: string;
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs(newsItem.id)}>
            <Head title="Edit news" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Edit news</h1>
                        <p className="text-sm text-muted-foreground">
                            Update content, visibility, and category placement.
                        </p>
                    </div>

                    <Button variant="outline" asChild>
                        <Link href={index()}>Back to news</Link>
                    </Button>
                </div>

                {status && (
                    <div className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {status}
                    </div>
                )}

                <NewsForm
                    action={update.form(newsItem.id)}
                    categories={categories}
                    newsItem={newsItem}
                    submitLabel="Save changes"
                />

                <Form {...destroy.form(newsItem.id)}>
                    {({ processing }) => (
                        <Button variant="destructive" disabled={processing}>
                            Delete news
                        </Button>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}

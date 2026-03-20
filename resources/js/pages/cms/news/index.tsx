import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { create, edit, index } from '@/routes/cms/news';
import type { BreadcrumbItem } from '@/types';

type NewsListItem = {
    id: number;
    status: string;
    published_at: string | null;
    featured_until: string | null;
    translations: Record<string, { title: string; slug: string }>;
    categories: Array<{ id: number; name: string }>;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'News',
        href: index(),
    },
];

export default function NewsIndex({
    newsItems,
    filters,
    status,
}: {
    newsItems: NewsListItem[];
    filters: { status?: string | null };
    status?: string;
}) {
    const statusFilters = [
        { label: 'All', value: null },
        { label: 'Draft', value: 'draft' },
        { label: 'In Review', value: 'in_review' },
        { label: 'Published', value: 'published' },
        { label: 'Archived', value: 'archived' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="News" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">News</h1>
                        <p className="text-sm text-muted-foreground">
                            Manage multilingual news, updates, and
                            announcements.
                        </p>
                    </div>

                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Create news
                        </Link>
                    </Button>
                </div>

                {status && (
                    <div className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {status}
                    </div>
                )}

                <div className="flex flex-wrap gap-2">
                    {statusFilters.map((statusFilter) => {
                        const isActive =
                            (filters.status ?? null) === statusFilter.value;

                        return (
                            <Button
                                key={statusFilter.label}
                                variant={isActive ? 'default' : 'outline'}
                                asChild
                            >
                                <Link
                                    href={
                                        statusFilter.value
                                            ? index({
                                                  query: {
                                                      status: statusFilter.value,
                                                  },
                                              })
                                            : index()
                                    }
                                    preserveState
                                    preserveScroll
                                >
                                    {statusFilter.label}
                                </Link>
                            </Button>
                        );
                    })}
                </div>

                <div className="overflow-hidden rounded-xl border">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-muted/40">
                            <tr>
                                <th className="px-4 py-3 font-medium">Title</th>
                                <th className="px-4 py-3 font-medium">Status</th>
                                <th className="px-4 py-3 font-medium">
                                    Categories
                                </th>
                                <th className="px-4 py-3 font-medium">
                                    Published
                                </th>
                                <th className="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {newsItems.map((newsItem) => (
                                <tr key={newsItem.id} className="border-t">
                                    <td className="px-4 py-3">
                                        <div className="font-medium">
                                            {newsItem.translations.en?.title ??
                                                `News #${newsItem.id}`}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            /
                                            {newsItem.translations.en?.slug ??
                                                ''}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 capitalize">
                                        {newsItem.status}
                                    </td>
                                    <td className="px-4 py-3">
                                        {newsItem.categories.length > 0
                                            ? newsItem.categories
                                                  .map(
                                                      (category) =>
                                                          category.name,
                                                  )
                                                  .join(', ')
                                            : '—'}
                                    </td>
                                    <td className="px-4 py-3">
                                        {newsItem.published_at ?? '—'}
                                    </td>
                                    <td className="px-4 py-3">
                                        <Link
                                            href={edit(newsItem.id)}
                                            className="text-sm underline"
                                        >
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                            ))}

                            {newsItems.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={5}
                                        className="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        No news created yet.
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

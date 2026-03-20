import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Activity, FileArchive, FileText, Inbox, Mail, Newspaper, ReceiptText, Users } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { index as auditLogsIndex } from '@/routes/cms/audit-logs';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

type DashboardFilters = {
    event: string;
    model: string;
    actor: string;
    date_from: string;
    date_to: string;
};

type ActivityItem = {
    id: number;
    event: string;
    event_label: string;
    model: string;
    record_id: number | null;
    actor: {
        name: string;
        email: string;
    } | null;
    ip_address: string | null;
    created_at: string | null;
};

type Option = {
    value: string;
    label: string;
};

export default function Dashboard({
    stats,
    activity,
    filters,
    filterOptions,
}: {
    stats: {
        published_pages: number;
        published_news: number;
        published_documents: number;
        active_procurements: number;
        new_grm_submissions: number;
        published_staff: number;
        active_subscriptions: number;
        recent_activity: number;
    };
    activity: ActivityItem[];
    filters: Partial<DashboardFilters>;
    filterOptions: {
        events: Option[];
        models: Option[];
    };
}) {
    const form = useForm<DashboardFilters>({
        event: filters.event ?? '',
        model: filters.model ?? '',
        actor: filters.actor ?? '',
        date_from: filters.date_from ?? '',
        date_to: filters.date_to ?? '',
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.transform((data) => Object.fromEntries(
            Object.entries(data).filter(([, value]) => value !== ''),
        ) as DashboardFilters);

        form.get(dashboard().url, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const reset = () => {
        form.setData({
            event: '',
            model: '',
            actor: '',
            date_from: '',
            date_to: '',
        });

        form.transform((data) => data);
        form.get(dashboard().url, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const overviewCards = [
        {
            title: 'Published pages',
            value: stats.published_pages,
            icon: FileText,
        },
        {
            title: 'Published news',
            value: stats.published_news,
            icon: Newspaper,
        },
        {
            title: 'Document archive',
            value: stats.published_documents,
            icon: FileArchive,
        },
        {
            title: 'Open procurements',
            value: stats.active_procurements,
            icon: ReceiptText,
        },
        {
            title: 'New GRM items',
            value: stats.new_grm_submissions,
            icon: Inbox,
        },
        {
            title: 'Published staff',
            value: stats.published_staff,
            icon: Users,
        },
        {
            title: 'Active subscriptions',
            value: stats.active_subscriptions,
            icon: Mail,
        },
        {
            title: 'Activity last 7 days',
            value: stats.recent_activity,
            icon: Activity,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <div className="space-y-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold">Dashboard</h1>
                    <p className="text-sm text-muted-foreground">
                        Monitor core portal modules and recent editorial activity.
                    </p>
                </div>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {overviewCards.map((card) => (
                        <section key={card.title} className="rounded-xl border bg-card p-4">
                            <div className="flex items-start justify-between gap-3">
                                <div>
                                    <p className="text-sm text-muted-foreground">{card.title}</p>
                                    <p className="mt-2 text-3xl font-semibold">{card.value}</p>
                                </div>
                                <card.icon className="size-5 text-muted-foreground" />
                            </div>
                        </section>
                    ))}
                </div>

                <section className="rounded-xl border bg-card">
                    <div className="flex items-center justify-between gap-4 border-b px-4 py-4">
                        <div>
                            <h2 className="text-lg font-semibold">Activity log</h2>
                            <p className="text-sm text-muted-foreground">
                                Filter recent content and subscription activity.
                            </p>
                        </div>

                        <Button variant="outline" asChild>
                            <Link href={auditLogsIndex()}>Open full audit log</Link>
                        </Button>
                    </div>

                    <form onSubmit={submit} className="grid gap-4 border-b p-4 md:grid-cols-2 xl:grid-cols-6">
                        <select
                            value={form.data.event}
                            onChange={(event) => form.setData('event', event.target.value)}
                            className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                        >
                            <option value="">All events</option>
                            {filterOptions.events.map((option) => (
                                <option key={option.value} value={option.value}>
                                    {option.label}
                                </option>
                            ))}
                        </select>

                        <select
                            value={form.data.model}
                            onChange={(event) => form.setData('model', event.target.value)}
                            className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                        >
                            <option value="">All modules</option>
                            {filterOptions.models.map((option) => (
                                <option key={option.value} value={option.value}>
                                    {option.label}
                                </option>
                            ))}
                        </select>

                        <Input
                            value={form.data.actor}
                            onChange={(event) => form.setData('actor', event.target.value)}
                            placeholder="Search actor"
                        />

                        <Input
                            type="date"
                            value={form.data.date_from}
                            onChange={(event) => form.setData('date_from', event.target.value)}
                        />

                        <Input
                            type="date"
                            value={form.data.date_to}
                            onChange={(event) => form.setData('date_to', event.target.value)}
                        />

                        <div className="flex gap-2">
                            <Button type="submit" disabled={form.processing}>Filter</Button>
                            <Button type="button" variant="outline" onClick={reset}>Reset</Button>
                        </div>
                    </form>

                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-muted/40">
                                <tr>
                                    <th className="px-4 py-3 font-medium">When</th>
                                    <th className="px-4 py-3 font-medium">Event</th>
                                    <th className="px-4 py-3 font-medium">Module</th>
                                    <th className="px-4 py-3 font-medium">Record</th>
                                    <th className="px-4 py-3 font-medium">Actor</th>
                                    <th className="px-4 py-3 font-medium">IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                {activity.map((item) => (
                                    <tr key={item.id} className="border-t">
                                        <td className="px-4 py-3 whitespace-nowrap">{item.created_at ?? '—'}</td>
                                        <td className="px-4 py-3">{item.event_label}</td>
                                        <td className="px-4 py-3">{item.model}</td>
                                        <td className="px-4 py-3">{item.record_id ? `#${item.record_id}` : '—'}</td>
                                        <td className="px-4 py-3">
                                            {item.actor ? (
                                                <div>
                                                    <div className="font-medium">{item.actor.name}</div>
                                                    <div className="text-xs text-muted-foreground">{item.actor.email}</div>
                                                </div>
                                            ) : 'Public'}
                                        </td>
                                        <td className="px-4 py-3">{item.ip_address ?? '—'}</td>
                                    </tr>
                                ))}

                                {activity.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="px-4 py-8 text-center text-muted-foreground">
                                            No activity matches the current filters.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}

import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Activity, Shield, UserRound, Users } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { index } from '@/routes/cms/audit-logs';
import type { BreadcrumbItem } from '@/types';

type AuditFilters = {
    event: string;
    model: string;
    actor: string;
    date_from: string;
    date_to: string;
};

type Option = {
    value: string;
    label: string;
};

type AuditLogItem = {
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
    user_agent: string | null;
    created_at: string | null;
    old_values: Record<string, unknown> | null;
    new_values: Record<string, unknown> | null;
    changed_fields: string[];
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
    {
        title: 'Audit log',
        href: index(),
    },
];

export default function AuditLogIndex({
    logs,
    filters,
    stats,
    filterOptions,
}: {
    logs: AuditLogItem[];
    filters: Partial<AuditFilters>;
    stats: {
        total: number;
        last_24_hours: number;
        public_actions: number;
        admin_actions: number;
    };
    filterOptions: {
        events: Option[];
        models: Option[];
    };
}) {
    const form = useForm<AuditFilters>({
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
        ) as AuditFilters);

        form.get(index().url, {
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
        form.get(index().url, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const overviewCards = [
        { title: 'Total log entries', value: stats.total, icon: Activity },
        { title: 'Last 24 hours', value: stats.last_24_hours, icon: Shield },
        { title: 'Admin actions', value: stats.admin_actions, icon: Users },
        { title: 'Public actions', value: stats.public_actions, icon: UserRound },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Audit log" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold">Audit log</h1>
                        <p className="text-sm text-muted-foreground">
                            Review administrative and public actions across the CMS.
                        </p>
                    </div>

                    <Button variant="outline" asChild>
                        <Link href={dashboard()}>Back to dashboard</Link>
                    </Button>
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

                <form onSubmit={submit} className="grid gap-4 rounded-xl border p-4 md:grid-cols-2 xl:grid-cols-6">
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

                <div className="overflow-hidden rounded-xl border">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-muted/40">
                            <tr>
                                <th className="px-4 py-3 font-medium">When</th>
                                <th className="px-4 py-3 font-medium">Event</th>
                                <th className="px-4 py-3 font-medium">Module</th>
                                <th className="px-4 py-3 font-medium">Record</th>
                                <th className="px-4 py-3 font-medium">Actor</th>
                                <th className="px-4 py-3 font-medium">Changed fields</th>
                                <th className="px-4 py-3 font-medium">IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            {logs.map((log) => (
                                <tr key={log.id} className="border-t align-top">
                                    <td className="px-4 py-3 whitespace-nowrap">{log.created_at ?? '—'}</td>
                                    <td className="px-4 py-3">{log.event_label}</td>
                                    <td className="px-4 py-3">{log.model}</td>
                                    <td className="px-4 py-3">{log.record_id ? `#${log.record_id}` : '—'}</td>
                                    <td className="px-4 py-3">
                                        {log.actor ? (
                                            <div>
                                                <div className="font-medium">{log.actor.name}</div>
                                                <div className="text-xs text-muted-foreground">{log.actor.email}</div>
                                            </div>
                                        ) : (
                                            'Public'
                                        )}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex flex-wrap gap-2">
                                            {log.changed_fields.length > 0 ? log.changed_fields.slice(0, 4).map((field) => (
                                                <span key={field} className="rounded-full border px-2 py-1 text-xs">
                                                    {field}
                                                </span>
                                            )) : '—'}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 text-xs text-muted-foreground">
                                        <div>{log.ip_address ?? '—'}</div>
                                        <div className="mt-1 max-w-48 truncate">{log.user_agent ?? '—'}</div>
                                    </td>
                                </tr>
                            ))}

                            {logs.length === 0 && (
                                <tr>
                                    <td colSpan={7} className="px-4 py-10 text-center text-muted-foreground">
                                        No audit entries match the current filters.
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

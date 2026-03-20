import { Form, Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '@/layouts/app-layout';
import { workflow } from '@/actions/App/Http/Controllers/Cms/SubscriptionController';
import { edit, index } from '@/routes/cms/subscriptions';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { BreadcrumbItem } from '@/types';

type SubscriptionListItem = {
    id: number;
    email: string;
    locale: string;
    status: string;
    source?: string | null;
    subscribed_at?: string | null;
    unsubscribed_at?: string | null;
    last_notified_at?: string | null;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Subscriptions',
        href: index(),
    },
];

type FilterState = {
    search: string;
    status: string;
    locale: string;
};

export default function SubscriptionIndex({
    subscriptions,
    filters,
    stats,
    status,
}: {
    subscriptions: SubscriptionListItem[];
    filters: Partial<FilterState>;
    stats: {
        total: number;
        active: number;
        unsubscribed: number;
        bounced: number;
    };
    status?: string;
}) {
    const form = useForm<FilterState>({
        search: filters.search ?? '',
        status: filters.status ?? '',
        locale: filters.locale ?? '',
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.transform((data) =>
            Object.fromEntries(
                Object.entries(data).filter(([, value]) => value !== ''),
            ) as unknown as FilterState,
        );

        form.get(index().url, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const reset = () => {
        form.setData({
            search: '',
            status: '',
            locale: '',
        });
        form.transform((data) => data);
        form.get(index().url, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Subscriptions" />

            <div className="space-y-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold">Subscriptions</h1>
                    <p className="text-sm text-muted-foreground">
                        Manage public email subscriptions and delivery status.
                    </p>
                </div>

                <div className="grid gap-4 md:grid-cols-4">
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">Total</p>
                        <p className="mt-2 text-3xl font-semibold">{stats.total}</p>
                    </div>
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">Active</p>
                        <p className="mt-2 text-3xl font-semibold text-emerald-600">{stats.active}</p>
                    </div>
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">Unsubscribed</p>
                        <p className="mt-2 text-3xl font-semibold text-amber-600">{stats.unsubscribed}</p>
                    </div>
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">Bounced</p>
                        <p className="mt-2 text-3xl font-semibold text-rose-600">{stats.bounced}</p>
                    </div>
                </div>

                {status && (
                    <div className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {status}
                    </div>
                )}

                <form onSubmit={submit} className="grid gap-4 rounded-xl border p-4 md:grid-cols-[2fr_1fr_1fr_auto]">
                    <Input
                        value={form.data.search}
                        onChange={(event) => form.setData('search', event.target.value)}
                        placeholder="Search by email"
                    />
                    <select
                        value={form.data.status}
                        onChange={(event) => form.setData('status', event.target.value)}
                        className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                    >
                        <option value="">All statuses</option>
                        <option value="active">Active</option>
                        <option value="unsubscribed">Unsubscribed</option>
                        <option value="bounced">Bounced</option>
                    </select>
                    <select
                        value={form.data.locale}
                        onChange={(event) => form.setData('locale', event.target.value)}
                        className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                    >
                        <option value="">All locales</option>
                        <option value="en">English</option>
                        <option value="tj">Tajik</option>
                        <option value="ru">Russian</option>
                    </select>
                    <div className="flex gap-2">
                        <Button type="submit" disabled={form.processing}>Filter</Button>
                        <Button type="button" variant="outline" onClick={reset}>Reset</Button>
                    </div>
                </form>

                <div className="overflow-hidden rounded-xl border">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-muted/40">
                            <tr>
                                <th className="px-4 py-3 font-medium">Email</th>
                                <th className="px-4 py-3 font-medium">Locale</th>
                                <th className="px-4 py-3 font-medium">Status</th>
                                <th className="px-4 py-3 font-medium">Source</th>
                                <th className="px-4 py-3 font-medium">Subscribed</th>
                                <th className="px-4 py-3 font-medium">Last notified</th>
                                <th className="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {subscriptions.map((subscription) => (
                                <tr key={subscription.id} className="border-t">
                                    <td className="px-4 py-3 font-medium">{subscription.email}</td>
                                    <td className="px-4 py-3 uppercase">{subscription.locale}</td>
                                    <td className="px-4 py-3 capitalize">{subscription.status}</td>
                                    <td className="px-4 py-3">{subscription.source ?? '—'}</td>
                                    <td className="px-4 py-3">{subscription.subscribed_at ?? '—'}</td>
                                    <td className="px-4 py-3">{subscription.last_notified_at ?? '—'}</td>
                                    <td className="px-4 py-3">
                                        <div className="flex flex-wrap gap-3">
                                            <Link href={edit(subscription.id)} className="text-sm underline">
                                                Edit
                                            </Link>
                                            {subscription.status !== 'unsubscribed' && (
                                                <Form
                                                    {...workflow.form(subscription.id)}
                                                    options={{ preserveScroll: true }}
                                                >
                                                    {({ processing }) => (
                                                        <>
                                                            <input type="hidden" name="status" value="unsubscribed" />
                                                            <button
                                                                type="submit"
                                                                disabled={processing}
                                                                className="text-sm underline"
                                                            >
                                                                Unsubscribe
                                                            </button>
                                                        </>
                                                    )}
                                                </Form>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}

                            {subscriptions.length === 0 && (
                                <tr>
                                    <td colSpan={7} className="px-4 py-8 text-center text-muted-foreground">
                                        No subscriptions yet.
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

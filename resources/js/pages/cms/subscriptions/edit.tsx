import { Form, Head } from '@inertiajs/react';
import {
    update,
    workflow,
} from '@/actions/App/Http/Controllers/Cms/SubscriptionController';
import WorkflowActions from '@/components/cms/workflow-actions';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { edit, index } from '@/routes/cms/subscriptions';
import type { BreadcrumbItem } from '@/types';

type SubscriptionData = {
    id: number;
    email: string;
    locale: string;
    status: string;
    source?: string | null;
    subscribed_at?: string | null;
    unsubscribed_at?: string | null;
    last_notified_at?: string | null;
    notes?: string | null;
};

export default function EditSubscription({
    subscription,
    status,
}: {
    subscription: SubscriptionData;
    status?: string;
}) {
    const workflowActions = [
        ...(subscription.status !== 'active'
            ? [{ label: 'Reactivate', status: 'active' as const, variant: 'default' as const }]
            : []),
        ...(subscription.status !== 'unsubscribed'
            ? [{ label: 'Unsubscribe', status: 'unsubscribed' as const, variant: 'outline' as const }]
            : []),
        ...(subscription.status !== 'bounced'
            ? [{ label: 'Mark bounced', status: 'bounced' as const, variant: 'secondary' as const }]
            : []),
    ];

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Subscriptions',
            href: index(),
        },
        {
            title: subscription.email,
            href: edit(subscription.id),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit subscription" />

            <div className="space-y-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold">Edit subscription</h1>
                    <p className="text-sm text-muted-foreground">
                        Update subscriber status and metadata.
                    </p>
                </div>

                {status && (
                    <div className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {status}
                    </div>
                )}

                <WorkflowActions
                    action={workflow.form(subscription.id)}
                    actions={workflowActions}
                    description="Quickly reactivate, unsubscribe, or mark delivery issues without editing the full record."
                />

                <Form {...update.form(subscription.id)} options={{ preserveScroll: true }} className="space-y-8">
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-6 rounded-xl border p-6">
                                <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="email">Email</Label>
                                        <Input id="email" name="email" defaultValue={subscription.email} />
                                        <InputError message={errors.email} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="source">Source</Label>
                                        <Input id="source" name="source" defaultValue={subscription.source ?? ''} />
                                        <InputError message={errors.source} />
                                    </div>
                                </div>

                                <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="locale">Locale</Label>
                                        <select
                                            id="locale"
                                            name="locale"
                                            defaultValue={subscription.locale}
                                            className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                        >
                                            <option value="en">English</option>
                                            <option value="tj">Tajik</option>
                                            <option value="ru">Russian</option>
                                        </select>
                                        <InputError message={errors.locale} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="status">Status</Label>
                                        <select
                                            id="status"
                                            name="status"
                                            defaultValue={subscription.status}
                                            className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                        >
                                            <option value="active">Active</option>
                                            <option value="unsubscribed">Unsubscribed</option>
                                            <option value="bounced">Bounced</option>
                                        </select>
                                        <InputError message={errors.status} />
                                    </div>
                                </div>

                                <div className="grid gap-2 md:grid-cols-3 md:gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="subscribed_at">Subscribed at</Label>
                                        <Input id="subscribed_at" name="subscribed_at" type="datetime-local" defaultValue={subscription.subscribed_at ?? ''} />
                                        <InputError message={errors.subscribed_at} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="unsubscribed_at">Unsubscribed at</Label>
                                        <Input id="unsubscribed_at" name="unsubscribed_at" type="datetime-local" defaultValue={subscription.unsubscribed_at ?? ''} />
                                        <InputError message={errors.unsubscribed_at} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="last_notified_at">Last notified at</Label>
                                        <Input id="last_notified_at" name="last_notified_at" type="datetime-local" defaultValue={subscription.last_notified_at ?? ''} />
                                        <InputError message={errors.last_notified_at} />
                                    </div>
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="notes">Notes</Label>
                                    <textarea
                                        id="notes"
                                        name="notes"
                                        defaultValue={subscription.notes ?? ''}
                                        className="border-input focus-visible:border-ring focus-visible:ring-ring/50 min-h-24 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                    />
                                    <InputError message={errors.notes} />
                                </div>
                            </div>

                            <Button disabled={processing}>Save subscription</Button>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}

import { Form, Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { workflow } from '@/actions/App/Http/Controllers/Cms/GrmSubmissionController';
import { create, edit, index } from '@/routes/cms/grm-submissions';
import type { BreadcrumbItem } from '@/types';

type SubmissionListItem = {
    id: number;
    reference_number: string;
    name: string;
    subject: string;
    status: string;
    submitted_at: string | null;
    assignee: string | null;
    notes_count: number;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'GRM',
        href: index(),
    },
];

export default function GrmSubmissionIndex({
    submissions,
    filters,
    stats,
    users,
    status,
}: {
    submissions: SubmissionListItem[];
    filters: {
        search?: string | null;
        status?: string | null;
        assigned_to?: number | null;
    };
    stats: {
        total: number;
        new: number;
        in_progress: number;
        resolved: number;
    };
    users: Array<{ id: number; name: string }>;
    status?: string;
}) {
    const form = useForm({
        search: filters.search ?? '',
        status: filters.status ?? '',
        assigned_to: filters.assigned_to ? String(filters.assigned_to) : '',
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.transform((data) =>
            Object.fromEntries(
                Object.entries(data).filter(([, value]) => value !== ''),
            ),
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
            assigned_to: '',
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
            <Head title="GRM submissions" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">GRM</h1>
                        <p className="text-sm text-muted-foreground">
                            Manage grievance submissions, assignment, and
                            internal notes.
                        </p>
                    </div>

                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Create submission
                        </Link>
                    </Button>
                </div>

                <div className="grid gap-4 md:grid-cols-4">
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">Total</p>
                        <p className="mt-2 text-3xl font-semibold">{stats.total}</p>
                    </div>
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">New</p>
                        <p className="mt-2 text-3xl font-semibold text-sky-600">{stats.new}</p>
                    </div>
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">In progress</p>
                        <p className="mt-2 text-3xl font-semibold text-amber-600">{stats.in_progress}</p>
                    </div>
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">Resolved</p>
                        <p className="mt-2 text-3xl font-semibold text-emerald-600">{stats.resolved}</p>
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
                        placeholder="Search ref, name, or subject"
                    />
                    <select
                        value={form.data.status}
                        onChange={(event) => form.setData('status', event.target.value)}
                        className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                    >
                        <option value="">All statuses</option>
                        <option value="new">New</option>
                        <option value="under_review">Under review</option>
                        <option value="in_progress">In progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                    <select
                        value={form.data.assigned_to}
                        onChange={(event) => form.setData('assigned_to', event.target.value)}
                        className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                    >
                        <option value="">All assignees</option>
                        {users.map((user) => (
                            <option key={user.id} value={String(user.id)}>
                                {user.name}
                            </option>
                        ))}
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
                                <th className="px-4 py-3 font-medium">Ref</th>
                                <th className="px-4 py-3 font-medium">Name</th>
                                <th className="px-4 py-3 font-medium">
                                    Subject
                                </th>
                                <th className="px-4 py-3 font-medium">Status</th>
                                <th className="px-4 py-3 font-medium">
                                    Assignee
                                </th>
                                <th className="px-4 py-3 font-medium">Notes</th>
                                <th className="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {submissions.map((submission) => (
                                <tr key={submission.id} className="border-t">
                                    <td className="px-4 py-3 font-medium">
                                        {submission.reference_number}
                                    </td>
                                    <td className="px-4 py-3">
                                        {submission.name}
                                    </td>
                                    <td className="px-4 py-3">
                                        {submission.subject}
                                    </td>
                                    <td className="px-4 py-3 capitalize">
                                        {submission.status.replace('_', ' ')}
                                    </td>
                                    <td className="px-4 py-3">
                                        {submission.assignee ?? '—'}
                                    </td>
                                    <td className="px-4 py-3">
                                        {submission.notes_count}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex flex-wrap gap-3">
                                            <Link
                                                href={edit(submission.id)}
                                                className="text-sm underline"
                                            >
                                                Edit
                                            </Link>
                                            {submission.status !== 'resolved' && (
                                                <Form
                                                    {...workflow.form(submission.id)}
                                                    options={{ preserveScroll: true }}
                                                >
                                                    {({ processing }) => (
                                                        <>
                                                            <input type="hidden" name="status" value="resolved" />
                                                            <button
                                                                type="submit"
                                                                disabled={processing}
                                                                className="text-sm underline"
                                                            >
                                                                Resolve
                                                            </button>
                                                        </>
                                                    )}
                                                </Form>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}

                            {submissions.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        No submissions created yet.
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

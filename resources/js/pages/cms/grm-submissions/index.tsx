import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
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
    status,
}: {
    submissions: SubmissionListItem[];
    status?: string;
}) {
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

                {status && (
                    <div className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {status}
                    </div>
                )}

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
                                        <Link
                                            href={edit(submission.id)}
                                            className="text-sm underline"
                                        >
                                            Edit
                                        </Link>
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

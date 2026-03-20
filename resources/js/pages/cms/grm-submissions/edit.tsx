import { Form, Head, Link } from '@inertiajs/react';
import {
    destroy,
    update,
    workflow,
} from '@/actions/App/Http/Controllers/Cms/GrmSubmissionController';
import WorkflowActions from '@/components/cms/workflow-actions';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import GrmSubmissionForm from '@/pages/cms/grm-submissions/form';
import { edit, index } from '@/routes/cms/grm-submissions';
import type { BreadcrumbItem } from '@/types';

type UserOption = {
    id: number;
    name: string;
};

type Note = {
    id: number;
    note: string;
    user: string | null;
    created_at: string | null;
};

type SubmissionFormData = {
    id: number;
    reference_number: string;
    name: string;
    email?: string | null;
    phone?: string | null;
    subject: string;
    message: string;
    status: string;
    submitted_at: string;
    reviewed_at?: string | null;
    resolved_at?: string | null;
    assigned_to?: number | null;
    notes: Note[];
};

const breadcrumbs = (submissionId: number): BreadcrumbItem[] => [
    {
        title: 'GRM',
        href: index(),
    },
    {
        title: 'Edit',
        href: edit(submissionId),
    },
];

export default function EditGrmSubmission({
    submission,
    users,
    status,
}: {
    submission: SubmissionFormData;
    users: UserOption[];
    status?: string;
}) {
    const workflowActions = [
        ...(submission.status !== 'under_review'
            ? [{ label: 'Start review', status: 'under_review' as const, variant: 'secondary' as const }]
            : []),
        ...(submission.status !== 'in_progress'
            ? [{ label: 'Mark in progress', status: 'in_progress' as const, variant: 'default' as const }]
            : []),
        ...(submission.status !== 'resolved'
            ? [{ label: 'Resolve', status: 'resolved' as const, variant: 'outline' as const }]
            : []),
        ...(submission.status !== 'closed'
            ? [{ label: 'Close case', status: 'closed' as const, variant: 'outline' as const }]
            : []),
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs(submission.id)}>
            <Head title="Edit GRM submission" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">
                            Edit GRM submission
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Update case handling, assignment, and internal
                            notes.
                        </p>
                    </div>

                    <Button variant="outline" asChild>
                        <Link href={index()}>Back to GRM</Link>
                    </Button>
                </div>

                {status && (
                    <div className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {status}
                    </div>
                )}

                <WorkflowActions
                    action={workflow.form(submission.id)}
                    actions={workflowActions}
                    description="Move this grievance through review, active handling, resolution, and closure."
                />

                <GrmSubmissionForm
                    action={update.form(submission.id)}
                    users={users}
                    submission={submission}
                    submitLabel="Save changes"
                />

                <Form {...destroy.form(submission.id)}>
                    {({ processing }) => (
                        <Button variant="destructive" disabled={processing}>
                            Delete submission
                        </Button>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}

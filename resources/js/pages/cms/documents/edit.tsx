import { Form, Head, Link } from '@inertiajs/react';
import {
    destroy,
    update,
    workflow,
} from '@/actions/App/Http/Controllers/Cms/DocumentController';
import WorkflowActions from '@/components/cms/workflow-actions';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { ContentBlock } from '@/lib/content-blocks';
import DocumentForm from '@/pages/cms/documents/form';
import { edit, index } from '@/routes/cms/documents';
import type { BreadcrumbItem } from '@/types';

type SelectOption = {
    id: number;
    name: string;
};

type DocumentFormData = {
    id: number;
    document_category_id: number;
    status: string;
    file_type?: string | null;
    document_date: string | null;
    published_at: string | null;
    archived_at: string | null;
    tag_ids: number[];
    file_url?: string | null;
    current_file?: {
        id: number;
        name: string;
        url: string;
    } | null;
    translations: Record<
        'en' | 'tj' | 'ru',
        {
            title: string;
            slug: string;
            summary?: string | null;
            content?: string | null;
            content_blocks?: ContentBlock[] | null;
            seo_title?: string | null;
            seo_description?: string | null;
        }
    >;
};

const breadcrumbs = (documentId: number): BreadcrumbItem[] => [
    {
        title: 'Documents',
        href: index(),
    },
    {
        title: 'Edit',
        href: edit(documentId),
    },
];

export default function EditDocument({
    document,
    categories,
    tags,
    availableStatuses,
    canPublish,
    status,
}: {
    document: DocumentFormData;
    categories: SelectOption[];
    tags: SelectOption[];
    availableStatuses: Array<{ value: string; label: string }>;
    canPublish: boolean;
    status?: string;
}) {
    const workflowActions = [
        ...(document.status !== 'draft'
            ? [
                  {
                      label: 'Move to draft',
                      status: 'draft' as const,
                      variant: 'outline' as const,
                  },
              ]
            : []),
        ...(document.status !== 'in_review'
            ? [
                  {
                      label: 'Send to review',
                      status: 'in_review' as const,
                      variant: 'secondary' as const,
                  },
              ]
            : []),
        ...(canPublish && document.status !== 'published'
            ? [
                  {
                      label: 'Publish',
                      status: 'published' as const,
                      variant: 'default' as const,
                  },
              ]
            : []),
        ...(canPublish && document.status !== 'archived'
            ? [
                  {
                      label: 'Archive',
                      status: 'archived' as const,
                      variant: 'outline' as const,
                  },
              ]
            : []),
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs(document.id)}>
            <Head title="Edit document" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Edit document</h1>
                        <p className="text-sm text-muted-foreground">
                            Update metadata, tags, and the downloadable file.
                        </p>
                    </div>

                    <Button variant="outline" asChild>
                        <Link href={index()}>Back to documents</Link>
                    </Button>
                </div>

                {status && (
                    <div className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {status}
                    </div>
                )}

                <WorkflowActions
                    action={workflow.form(document.id)}
                    actions={workflowActions}
                />

                <DocumentForm
                    action={update.form(document.id)}
                    categories={categories}
                    tags={tags}
                    availableStatuses={availableStatuses}
                    document={document}
                    submitLabel="Save changes"
                />

                <Form {...destroy.form(document.id)}>
                    {({ processing }) => (
                        <Button variant="destructive" disabled={processing}>
                            Delete document
                        </Button>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}

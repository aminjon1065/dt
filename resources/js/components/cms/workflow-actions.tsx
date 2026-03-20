import { Form } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

type WorkflowAction = {
    label: string;
    status: string;
    variant?: 'default' | 'outline' | 'secondary';
};

type Props = {
    action: any;
    actions: WorkflowAction[];
    description?: string;
};

export default function WorkflowActions({ action, actions, description }: Props) {
    if (actions.length === 0) {
        return null;
    }

    return (
        <div className="rounded-xl border p-4">
            <div className="mb-3">
                <h2 className="text-sm font-semibold">Workflow actions</h2>
                <p className="text-sm text-muted-foreground">
                    {description ?? 'Move this record through its next operational status changes.'}
                </p>
            </div>

            <div className="flex flex-wrap gap-3">
                {actions.map((workflowAction) => (
                    <Form
                        key={workflowAction.status}
                        {...action}
                        options={{ preserveScroll: true }}
                    >
                        {({ processing }) => (
                            <>
                                <input
                                    type="hidden"
                                    name="status"
                                    value={workflowAction.status}
                                />
                                <Button
                                    variant={
                                        workflowAction.variant ?? 'outline'
                                    }
                                    disabled={processing}
                                >
                                    {workflowAction.label}
                                </Button>
                            </>
                        )}
                    </Form>
                ))}
            </div>
        </div>
    );
}

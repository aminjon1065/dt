import { Form } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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
    notes?: Note[];
};

type Props = {
    action: any;
    users: UserOption[];
    submission?: SubmissionFormData;
    submitLabel: string;
};

export default function GrmSubmissionForm({
    action,
    users,
    submission,
    submitLabel,
}: Props) {
    return (
        <Form
            {...action}
            options={{ preserveScroll: true }}
            className="space-y-8"
        >
            {({ errors, processing }) => (
                <>
                    <div className="grid gap-6 rounded-xl border p-6">
                        <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="reference_number">
                                    Reference number
                                </Label>
                                <Input
                                    id="reference_number"
                                    name="reference_number"
                                    defaultValue={
                                        submission?.reference_number ?? ''
                                    }
                                />
                                <InputError
                                    message={errors.reference_number}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="status">Status</Label>
                                <select
                                    id="status"
                                    name="status"
                                    defaultValue={submission?.status ?? 'new'}
                                    className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                >
                                    <option value="new">New</option>
                                    <option value="under_review">
                                        Under review
                                    </option>
                                    <option value="in_progress">
                                        In progress
                                    </option>
                                    <option value="resolved">Resolved</option>
                                    <option value="closed">Closed</option>
                                </select>
                                <InputError message={errors.status} />
                            </div>
                        </div>

                        <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={submission?.name ?? ''}
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="subject">Subject</Label>
                                <Input
                                    id="subject"
                                    name="subject"
                                    defaultValue={submission?.subject ?? ''}
                                />
                                <InputError message={errors.subject} />
                            </div>
                        </div>

                        <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    name="email"
                                    defaultValue={submission?.email ?? ''}
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="phone">Phone</Label>
                                <Input
                                    id="phone"
                                    name="phone"
                                    defaultValue={submission?.phone ?? ''}
                                />
                                <InputError message={errors.phone} />
                            </div>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="message">Message</Label>
                            <textarea
                                id="message"
                                name="message"
                                defaultValue={submission?.message ?? ''}
                                className="border-input focus-visible:border-ring focus-visible:ring-ring/50 min-h-32 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                            />
                            <InputError message={errors.message} />
                        </div>

                        <div className="grid gap-2 md:grid-cols-4 md:gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="submitted_at">
                                    Submitted at
                                </Label>
                                <Input
                                    id="submitted_at"
                                    name="submitted_at"
                                    type="datetime-local"
                                    defaultValue={
                                        submission?.submitted_at ?? ''
                                    }
                                />
                                <InputError message={errors.submitted_at} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="reviewed_at">
                                    Reviewed at
                                </Label>
                                <Input
                                    id="reviewed_at"
                                    name="reviewed_at"
                                    type="datetime-local"
                                    defaultValue={submission?.reviewed_at ?? ''}
                                />
                                <InputError message={errors.reviewed_at} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="resolved_at">
                                    Resolved at
                                </Label>
                                <Input
                                    id="resolved_at"
                                    name="resolved_at"
                                    type="datetime-local"
                                    defaultValue={submission?.resolved_at ?? ''}
                                />
                                <InputError message={errors.resolved_at} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="assigned_to">Assigned to</Label>
                                <select
                                    id="assigned_to"
                                    name="assigned_to"
                                    defaultValue={submission?.assigned_to ?? ''}
                                    className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                >
                                    <option value="">Unassigned</option>
                                    {users.map((user) => (
                                        <option key={user.id} value={user.id}>
                                            {user.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.assigned_to} />
                            </div>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="note">Add note</Label>
                            <textarea
                                id="note"
                                name="note"
                                className="border-input focus-visible:border-ring focus-visible:ring-ring/50 min-h-24 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                            />
                            <InputError message={errors.note} />
                        </div>
                    </div>

                    {submission?.notes && submission.notes.length > 0 && (
                        <div className="grid gap-4 rounded-xl border p-6">
                            <h2 className="text-lg font-semibold">Notes</h2>
                            <div className="space-y-3">
                                {submission.notes.map((note) => (
                                    <div
                                        key={note.id}
                                        className="rounded-md border p-3"
                                    >
                                        <div className="text-xs text-muted-foreground">
                                            {note.user ?? 'Unknown'} •{' '}
                                            {note.created_at ?? ''}
                                        </div>
                                        <div className="mt-2 text-sm">
                                            {note.note}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    <Button disabled={processing}>{submitLabel}</Button>
                </>
            )}
        </Form>
    );
}

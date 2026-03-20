import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { Plus, Users } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { create, edit, index } from '@/routes/cms/staff-members';
import type { BreadcrumbItem } from '@/types';

type StaffMemberListItem = {
    id: number;
    parent_id: number | null;
    parent_name?: string | null;
    email?: string | null;
    phone?: string | null;
    status: string;
    published_at: string | null;
    sort_order: number;
    translations: Record<string, { name: string; position?: string | null }>;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Staff',
        href: index(),
    },
];

export default function StaffMemberIndex({
    staffMembers,
    filters,
    stats,
    parentStaffMembers,
    status,
}: {
    staffMembers: StaffMemberListItem[];
    filters: {
        search?: string | null;
        status?: string | null;
        parent_id?: number | null;
    };
    stats: {
        total: number;
        published: number;
        draft: number;
        archived: number;
    };
    parentStaffMembers: Array<{ id: number; name: string }>;
    status?: string;
}) {
    const form = useForm({
        search: filters.search ?? '',
        status: filters.status ?? '',
        parent_id: filters.parent_id ? String(filters.parent_id) : '',
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
            parent_id: '',
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
            <Head title="Staff directory" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Staff directory</h1>
                        <p className="text-sm text-muted-foreground">
                            Manage hierarchical staff profiles for the public directory.
                        </p>
                    </div>

                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Add staff member
                        </Link>
                    </Button>
                </div>

                <div className="grid gap-4 md:grid-cols-4">
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">Total</p>
                        <p className="mt-2 text-3xl font-semibold">{stats.total}</p>
                    </div>
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">Published</p>
                        <p className="mt-2 text-3xl font-semibold text-emerald-600">{stats.published}</p>
                    </div>
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">Draft</p>
                        <p className="mt-2 text-3xl font-semibold text-amber-600">{stats.draft}</p>
                    </div>
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">Archived</p>
                        <p className="mt-2 text-3xl font-semibold text-slate-600">{stats.archived}</p>
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
                        placeholder="Search by name or position"
                    />
                    <select
                        value={form.data.status}
                        onChange={(event) => form.setData('status', event.target.value)}
                        className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                    >
                        <option value="">All statuses</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                        <option value="archived">Archived</option>
                    </select>
                    <select
                        value={form.data.parent_id}
                        onChange={(event) => form.setData('parent_id', event.target.value)}
                        className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                    >
                        <option value="">All reporting lines</option>
                        {parentStaffMembers.map((staffMember) => (
                            <option key={staffMember.id} value={String(staffMember.id)}>
                                {staffMember.name}
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
                                <th className="px-4 py-3 font-medium">Name</th>
                                <th className="px-4 py-3 font-medium">Position</th>
                                <th className="px-4 py-3 font-medium">Reports to</th>
                                <th className="px-4 py-3 font-medium">Status</th>
                                <th className="px-4 py-3 font-medium">Contact</th>
                                <th className="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {staffMembers.map((staffMember) => (
                                <tr key={staffMember.id} className="border-t">
                                    <td className="px-4 py-3 font-medium">
                                        {staffMember.translations.en?.name ?? `Staff Member #${staffMember.id}`}
                                    </td>
                                    <td className="px-4 py-3">
                                        {staffMember.translations.en?.position ?? '—'}
                                    </td>
                                    <td className="px-4 py-3">
                                        {staffMember.parent_name ?? '—'}
                                    </td>
                                    <td className="px-4 py-3 capitalize">
                                        {staffMember.status}
                                    </td>
                                    <td className="px-4 py-3 text-xs text-muted-foreground">
                                        {staffMember.email ?? staffMember.phone ?? 'No contact'}
                                    </td>
                                    <td className="px-4 py-3">
                                        <Link href={edit(staffMember.id)} className="text-sm underline">
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                            ))}

                            {staffMembers.length === 0 && (
                                <tr>
                                    <td colSpan={6} className="px-4 py-10 text-center text-muted-foreground">
                                        <div className="flex flex-col items-center gap-2">
                                            <Users className="size-5" />
                                            No staff members created yet.
                                        </div>
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

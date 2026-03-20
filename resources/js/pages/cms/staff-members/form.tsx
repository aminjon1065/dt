import { Form } from '@inertiajs/react';
import { LocaleTabs } from '@/components/cms/locale-tabs';
import MediaManager from '@/components/cms/media-manager';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type WayfinderFormAction } from '@/lib/locales';
import { NativeSelect } from '@/components/ui/native-select';

type TranslationFields = {
    name: string;
    slug: string;
    position?: string | null;
    bio?: string | null;
    seo_title?: string | null;
    seo_description?: string | null;
};

type StaffMemberFormData = {
    parent_id: number | null;
    email?: string | null;
    phone?: string | null;
    office_location?: string | null;
    show_email_publicly: boolean;
    show_phone_publicly: boolean;
    status: string;
    published_at: string | null;
    archived_at: string | null;
    sort_order: number;
    photo_url?: string | null;
    current_photo?: {
        id: number;
        name: string;
        url: string;
    } | null;
    translations: Record<'en' | 'tj' | 'ru', TranslationFields>;
};

type ParentStaffMember = {
    id: number;
    name: string;
};

type Props = {
    action: WayfinderFormAction;
    parentStaffMembers: ParentStaffMember[];
    staffMember?: StaffMemberFormData;
    submitLabel: string;
};

export default function StaffMemberForm({
    action,
    parentStaffMembers,
    staffMember,
    submitLabel,
}: Props) {
    return (
        <Form {...action} options={{ preserveScroll: true }} className="space-y-8">
            {({ errors, processing, recentlySuccessful }) => (
                <>
                    <div className="grid gap-6 rounded-xl border p-6">
                        <div className="grid gap-2">
                            <Label htmlFor="parent_id">Manager / parent</Label>
                            <NativeSelect
                                id="parent_id"
                                name="parent_id"
                                defaultValue={staffMember?.parent_id ?? ''}
                                className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                            >
                                <option value="">Top level profile</option>
                                {parentStaffMembers.map((parentStaffMember) => (
                                    <option key={parentStaffMember.id} value={parentStaffMember.id}>
                                        {parentStaffMember.name}
                                    </option>
                                ))}
                            </NativeSelect>
                            <InputError message={errors.parent_id} />
                        </div>

                        <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="email">Email</Label>
                                <Input id="email" name="email" type="email" defaultValue={staffMember?.email ?? ''} />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="phone">Phone</Label>
                                <Input id="phone" name="phone" defaultValue={staffMember?.phone ?? ''} />
                                <InputError message={errors.phone} />
                            </div>
                        </div>

                        <div className="grid gap-2 md:grid-cols-4 md:gap-4">
                            <div className="grid gap-2 md:col-span-2">
                                <Label htmlFor="office_location">Office location</Label>
                                <Input
                                    id="office_location"
                                    name="office_location"
                                    defaultValue={staffMember?.office_location ?? ''}
                                />
                                <InputError message={errors.office_location} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="status">Status</Label>
                                <NativeSelect
                                    id="status"
                                    name="status"
                                    defaultValue={staffMember?.status ?? 'draft'}
                                    className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </NativeSelect>
                                <InputError message={errors.status} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="sort_order">Sort order</Label>
                                <Input
                                    id="sort_order"
                                    name="sort_order"
                                    type="number"
                                    min={0}
                                    defaultValue={staffMember?.sort_order ?? 0}
                                />
                                <InputError message={errors.sort_order} />
                            </div>
                        </div>

                        <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="published_at">Published at</Label>
                                <Input
                                    id="published_at"
                                    name="published_at"
                                    type="datetime-local"
                                    defaultValue={staffMember?.published_at ?? ''}
                                />
                                <InputError message={errors.published_at} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="archived_at">Archived at</Label>
                                <Input
                                    id="archived_at"
                                    name="archived_at"
                                    type="datetime-local"
                                    defaultValue={staffMember?.archived_at ?? ''}
                                />
                                <InputError message={errors.archived_at} />
                            </div>
                        </div>

                        <MediaManager
                            inputId="photo"
                            inputName="photo"
                            label="Profile photo"
                            currentLabel="Current photo"
                            existingItems={staffMember?.current_photo ? [staffMember.current_photo] : []}
                            removeInputName="remove_photo"
                            removeInputType="boolean"
                            error={errors.photo}
                            removeError={errors.remove_photo}
                        />

                        <input type="hidden" name="show_email_publicly" value="0" />
                        <input type="hidden" name="show_phone_publicly" value="0" />

                        <div className="flex flex-col gap-4 md:flex-row md:items-center">
                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="show_email_publicly"
                                    name="show_email_publicly"
                                    value="1"
                                    defaultChecked={staffMember?.show_email_publicly ?? false}
                                />
                                <Label htmlFor="show_email_publicly">Show email publicly</Label>
                            </div>

                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="show_phone_publicly"
                                    name="show_phone_publicly"
                                    value="1"
                                    defaultChecked={staffMember?.show_phone_publicly ?? true}
                                />
                                <Label htmlFor="show_phone_publicly">Show phone publicly</Label>
                            </div>
                        </div>
                    </div>

                    <LocaleTabs errors={errors}>
                        {(locale) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor={`translations.${locale}.name`}>Name</Label>
                                    <Input
                                        id={`translations.${locale}.name`}
                                        name={`translations[${locale}][name]`}
                                        defaultValue={staffMember?.translations[locale]?.name ?? ''}
                                    />
                                    <InputError message={errors[`translations.${locale}.name`]} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor={`translations.${locale}.slug`}>Slug</Label>
                                    <Input
                                        id={`translations.${locale}.slug`}
                                        name={`translations[${locale}][slug]`}
                                        defaultValue={staffMember?.translations[locale]?.slug ?? ''}
                                    />
                                    <InputError message={errors[`translations.${locale}.slug`]} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor={`translations.${locale}.position`}>Position</Label>
                                    <Input
                                        id={`translations.${locale}.position`}
                                        name={`translations[${locale}][position]`}
                                        defaultValue={staffMember?.translations[locale]?.position ?? ''}
                                    />
                                    <InputError message={errors[`translations.${locale}.position`]} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor={`translations.${locale}.bio`}>Biography</Label>
                                    <textarea
                                        id={`translations.${locale}.bio`}
                                        name={`translations[${locale}][bio]`}
                                        defaultValue={staffMember?.translations[locale]?.bio ?? ''}
                                        className="border-input focus-visible:border-ring focus-visible:ring-ring/50 min-h-32 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                    />
                                    <InputError message={errors[`translations.${locale}.bio`]} />
                                </div>

                                <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor={`translations.${locale}.seo_title`}>SEO title</Label>
                                        <Input
                                            id={`translations.${locale}.seo_title`}
                                            name={`translations[${locale}][seo_title]`}
                                            defaultValue={staffMember?.translations[locale]?.seo_title ?? ''}
                                        />
                                        <InputError message={errors[`translations.${locale}.seo_title`]} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor={`translations.${locale}.seo_description`}>SEO description</Label>
                                        <textarea
                                            id={`translations.${locale}.seo_description`}
                                            name={`translations[${locale}][seo_description]`}
                                            defaultValue={staffMember?.translations[locale]?.seo_description ?? ''}
                                            className="border-input focus-visible:border-ring focus-visible:ring-ring/50 min-h-24 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                        />
                                        <InputError message={errors[`translations.${locale}.seo_description`]} />
                                    </div>
                                </div>
                            </>
                        )}
                    </LocaleTabs>

                    <div className="flex items-center gap-4">
                        <Button disabled={processing}>{submitLabel}</Button>
                        {recentlySuccessful && (
                            <p className="text-sm text-neutral-600">Saved</p>
                        )}
                    </div>
                </>
            )}
        </Form>
    );
}

import { Form } from '@inertiajs/react';
import RichTextEditor from '@/components/cms/rich-text-editor';
import { LocaleTabs } from '@/components/cms/locale-tabs';
import MediaManager from '@/components/cms/media-manager';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { ContentBlock } from '@/lib/content-blocks';
import { type WayfinderFormAction } from '@/lib/locales';
import { NativeSelect } from '@/components/ui/native-select';

type TranslationFields = {
    title: string;
    slug: string;
    summary?: string | null;
    content?: string | null;
    content_blocks?: ContentBlock[] | null;
    seo_title?: string | null;
    seo_description?: string | null;
};

type Attachment = {
    id: number;
    name: string;
    url: string;
};

type ProcurementFormData = {
    reference_number: string;
    procurement_type: string;
    status: string;
    published_at: string | null;
    closing_at: string | null;
    archived_at: string | null;
    attachments?: Attachment[];
    translations: Record<'en' | 'tj' | 'ru', TranslationFields>;
};

type Props = {
    action: WayfinderFormAction;
    availableStatuses: Array<{ value: string; label: string }>;
    procurement?: ProcurementFormData;
    submitLabel: string;
};

export default function ProcurementForm({
    action,
    availableStatuses,
    procurement,
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
                                        procurement?.reference_number ?? ''
                                    }
                                />
                                <InputError
                                    message={errors.reference_number}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="procurement_type">
                                    Procurement type
                                </Label>
                                <NativeSelect
                                    id="procurement_type"
                                    name="procurement_type"
                                    defaultValue={
                                        procurement?.procurement_type ??
                                        'goods'
                                    }
                                    className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                >
                                    <option value="goods">Goods</option>
                                    <option value="services">Services</option>
                                    <option value="works">Works</option>
                                    <option value="consulting">
                                        Consulting
                                    </option>
                                    <option value="other">Other</option>
                                </NativeSelect>
                                <InputError
                                    message={errors.procurement_type}
                                />
                            </div>
                        </div>

                        <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="status">Status</Label>
                                <NativeSelect
                                    id="status"
                                    name="status"
                                    defaultValue={
                                        procurement?.status ?? 'planned'
                                    }
                                    className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                >
                                    {availableStatuses.map((statusOption) => (
                                        <option key={statusOption.value} value={statusOption.value}>
                                            {statusOption.label}
                                        </option>
                                    ))}
                                </NativeSelect>
                                <InputError message={errors.status} />
                            </div>

                            <MediaManager
                                inputId="attachments"
                                inputName="attachments[]"
                                label="Attachments"
                                currentLabel="Current attachments"
                                existingItems={procurement?.attachments ?? []}
                                multiple
                                removeInputName="remove_attachment_ids[]"
                                removeInputType="array"
                                error={errors.attachments}
                                removeError={errors.remove_attachment_ids}
                            />
                        </div>

                        <div className="grid gap-2 md:grid-cols-3 md:gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="published_at">
                                    Published at
                                </Label>
                                <Input
                                    id="published_at"
                                    name="published_at"
                                    type="datetime-local"
                                    defaultValue={
                                        procurement?.published_at ?? ''
                                    }
                                />
                                <InputError message={errors.published_at} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="closing_at">Closing at</Label>
                                <Input
                                    id="closing_at"
                                    name="closing_at"
                                    type="datetime-local"
                                    defaultValue={
                                        procurement?.closing_at ?? ''
                                    }
                                />
                                <InputError message={errors.closing_at} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="archived_at">Archived at</Label>
                                <Input
                                    id="archived_at"
                                    name="archived_at"
                                    type="datetime-local"
                                    defaultValue={
                                        procurement?.archived_at ?? ''
                                    }
                                />
                                <InputError message={errors.archived_at} />
                            </div>
                        </div>
                    </div>

                    <LocaleTabs errors={errors}>
                        {(locale) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor={`translations.${locale}.title`}>Title</Label>
                                    <Input
                                        id={`translations.${locale}.title`}
                                        name={`translations[${locale}][title]`}
                                        defaultValue={procurement?.translations[locale]?.title ?? ''}
                                    />
                                    <InputError message={errors[`translations.${locale}.title`]} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor={`translations.${locale}.slug`}>Slug</Label>
                                    <Input
                                        id={`translations.${locale}.slug`}
                                        name={`translations[${locale}][slug]`}
                                        defaultValue={procurement?.translations[locale]?.slug ?? ''}
                                    />
                                    <InputError message={errors[`translations.${locale}.slug`]} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor={`translations.${locale}.summary`}>Summary</Label>
                                    <textarea
                                        id={`translations.${locale}.summary`}
                                        name={`translations[${locale}][summary]`}
                                        defaultValue={procurement?.translations[locale]?.summary ?? ''}
                                        className="border-input focus-visible:border-ring focus-visible:ring-ring/50 min-h-24 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                    />
                                    <InputError message={errors[`translations.${locale}.summary`]} />
                                </div>

                                <RichTextEditor
                                    name={`translations[${locale}][content]`}
                                    label="Content"
                                    initialHtml={procurement?.translations[locale]?.content ?? null}
                                    fallbackBlocks={procurement?.translations[locale]?.content_blocks ?? null}
                                />

                                <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor={`translations.${locale}.seo_title`}>SEO title</Label>
                                        <Input
                                            id={`translations.${locale}.seo_title`}
                                            name={`translations[${locale}][seo_title]`}
                                            defaultValue={procurement?.translations[locale]?.seo_title ?? ''}
                                        />
                                        <InputError message={errors[`translations.${locale}.seo_title`]} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor={`translations.${locale}.seo_description`}>SEO description</Label>
                                        <textarea
                                            id={`translations.${locale}.seo_description`}
                                            name={`translations[${locale}][seo_description]`}
                                            defaultValue={procurement?.translations[locale]?.seo_description ?? ''}
                                            className="border-input focus-visible:border-ring focus-visible:ring-ring/50 min-h-24 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                        />
                                        <InputError message={errors[`translations.${locale}.seo_description`]} />
                                    </div>
                                </div>
                            </>
                        )}
                    </LocaleTabs>

                    <Button disabled={processing}>{submitLabel}</Button>
                </>
            )}
        </Form>
    );
}

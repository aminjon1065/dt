import { Form } from '@inertiajs/react';
import RichTextEditor from '@/components/cms/rich-text-editor';
import { LocaleTabs } from '@/components/cms/locale-tabs';
import MediaManager from '@/components/cms/media-manager';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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

type DocumentFormData = {
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
    translations: Record<'en' | 'tj' | 'ru', TranslationFields>;
};

type SelectOption = {
    id: number;
    name: string;
};

type Props = {
    action: WayfinderFormAction;
    categories: SelectOption[];
    tags: SelectOption[];
    availableStatuses: Array<{ value: string; label: string }>;
    document?: DocumentFormData;
    submitLabel: string;
};

export default function DocumentForm({
    action,
    categories,
    tags,
    availableStatuses,
    document,
    submitLabel,
}: Props) {
    return (
        <Form
            {...action}
            options={{ preserveScroll: true }}
            className="space-y-8"
        >
            {({ errors, processing, recentlySuccessful }) => (
                <>
                    <div className="grid gap-6 rounded-xl border p-6">
                        <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="document_category_id">
                                    Category
                                </Label>
                                <NativeSelect
                                    id="document_category_id"
                                    name="document_category_id"
                                    defaultValue={
                                        document?.document_category_id ?? ''
                                    }
                                    className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                >
                                    <option value="" disabled>
                                        Select a category
                                    </option>
                                    {categories.map((category) => (
                                        <option
                                            key={category.id}
                                            value={category.id}
                                        >
                                            {category.name}
                                        </option>
                                    ))}
                                </NativeSelect>
                                <InputError
                                    message={errors.document_category_id}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="status">Status</Label>
                                <NativeSelect
                                    id="status"
                                    name="status"
                                    defaultValue={document?.status ?? 'draft'}
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
                        </div>

                        <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="file_type">File type</Label>
                                <Input
                                    id="file_type"
                                    name="file_type"
                                    defaultValue={document?.file_type ?? ''}
                                    placeholder="pdf"
                                />
                                <InputError message={errors.file_type} />
                            </div>

                            <MediaManager
                                inputId="file"
                                inputName="file"
                                label="File"
                                currentLabel="Current file"
                                existingItems={
                                    document?.current_file
                                        ? [document.current_file]
                                        : []
                                }
                                removeInputName="remove_file"
                                removeInputType="boolean"
                                error={errors.file}
                                removeError={errors.remove_file}
                            />
                        </div>

                        <div className="grid gap-2 md:grid-cols-3 md:gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="document_date">
                                    Document date
                                </Label>
                                <Input
                                    id="document_date"
                                    name="document_date"
                                    type="date"
                                    defaultValue={document?.document_date ?? ''}
                                />
                                <InputError message={errors.document_date} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="published_at">
                                    Published at
                                </Label>
                                <Input
                                    id="published_at"
                                    name="published_at"
                                    type="datetime-local"
                                    defaultValue={document?.published_at ?? ''}
                                />
                                <InputError message={errors.published_at} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="archived_at">Archived at</Label>
                                <Input
                                    id="archived_at"
                                    name="archived_at"
                                    type="datetime-local"
                                    defaultValue={document?.archived_at ?? ''}
                                />
                                <InputError message={errors.archived_at} />
                            </div>
                        </div>

                        <div className="grid gap-3">
                            <Label>Tags</Label>
                            <div className="grid gap-3 md:grid-cols-2">
                                {tags.map((tag) => (
                                    <label
                                        key={tag.id}
                                        className="flex items-center gap-3 rounded-md border px-3 py-2"
                                    >
                                        <Checkbox
                                            name="tag_ids[]"
                                            value={String(tag.id)}
                                            defaultChecked={document?.tag_ids.includes(
                                                tag.id,
                                            )}
                                        />
                                        <span className="text-sm">
                                            {tag.name}
                                        </span>
                                    </label>
                                ))}
                            </div>
                            <InputError message={errors.tag_ids} />
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
                                        defaultValue={document?.translations[locale]?.title ?? ''}
                                    />
                                    <InputError message={errors[`translations.${locale}.title`]} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor={`translations.${locale}.slug`}>Slug</Label>
                                    <Input
                                        id={`translations.${locale}.slug`}
                                        name={`translations[${locale}][slug]`}
                                        defaultValue={document?.translations[locale]?.slug ?? ''}
                                    />
                                    <InputError message={errors[`translations.${locale}.slug`]} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor={`translations.${locale}.summary`}>Summary</Label>
                                    <textarea
                                        id={`translations.${locale}.summary`}
                                        name={`translations[${locale}][summary]`}
                                        defaultValue={document?.translations[locale]?.summary ?? ''}
                                        className="border-input focus-visible:border-ring focus-visible:ring-ring/50 min-h-24 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                    />
                                    <InputError message={errors[`translations.${locale}.summary`]} />
                                </div>

                                <RichTextEditor
                                    name={`translations[${locale}][content]`}
                                    label="Description"
                                    initialHtml={document?.translations[locale]?.content ?? null}
                                    fallbackBlocks={document?.translations[locale]?.content_blocks ?? null}
                                />

                                <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor={`translations.${locale}.seo_title`}>SEO title</Label>
                                        <Input
                                            id={`translations.${locale}.seo_title`}
                                            name={`translations[${locale}][seo_title]`}
                                            defaultValue={document?.translations[locale]?.seo_title ?? ''}
                                        />
                                        <InputError message={errors[`translations.${locale}.seo_title`]} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor={`translations.${locale}.seo_description`}>SEO description</Label>
                                        <textarea
                                            id={`translations.${locale}.seo_description`}
                                            name={`translations[${locale}][seo_description]`}
                                            defaultValue={document?.translations[locale]?.seo_description ?? ''}
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

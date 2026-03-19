import { Form } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type TranslationFields = {
    title: string;
    slug: string;
    summary?: string | null;
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
    translations: Record<'en' | 'tj' | 'ru', TranslationFields>;
};

type SelectOption = {
    id: number;
    name: string;
};

type Props = {
    action: any;
    categories: SelectOption[];
    tags: SelectOption[];
    document?: DocumentFormData;
    submitLabel: string;
};

const locales: Array<'en' | 'tj' | 'ru'> = ['en', 'tj', 'ru'];

export default function DocumentForm({
    action,
    categories,
    tags,
    document,
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
                                <Label htmlFor="document_category_id">
                                    Category
                                </Label>
                                <select
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
                                </select>
                                <InputError
                                    message={errors.document_category_id}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="status">Status</Label>
                                <select
                                    id="status"
                                    name="status"
                                    defaultValue={document?.status ?? 'draft'}
                                    className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
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

                            <div className="grid gap-2">
                                <Label htmlFor="file">File</Label>
                                <Input id="file" name="file" type="file" />
                                {document?.file_url && (
                                    <a
                                        href={document.file_url}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="text-sm underline"
                                    >
                                        Open current file
                                    </a>
                                )}
                                <InputError message={errors.file} />
                            </div>
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

                    {locales.map((locale) => (
                        <div
                            key={locale}
                            className="grid gap-4 rounded-xl border p-6"
                        >
                            <h2 className="text-lg font-semibold uppercase">
                                {locale}
                            </h2>

                            <div className="grid gap-2">
                                <Label htmlFor={`translations.${locale}.title`}>
                                    Title
                                </Label>
                                <Input
                                    id={`translations.${locale}.title`}
                                    name={`translations[${locale}][title]`}
                                    defaultValue={
                                        document?.translations[locale]?.title ??
                                        ''
                                    }
                                />
                                <InputError
                                    message={errors[`translations.${locale}.title`]}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor={`translations.${locale}.slug`}>
                                    Slug
                                </Label>
                                <Input
                                    id={`translations.${locale}.slug`}
                                    name={`translations[${locale}][slug]`}
                                    defaultValue={
                                        document?.translations[locale]?.slug ??
                                        ''
                                    }
                                />
                                <InputError
                                    message={errors[`translations.${locale}.slug`]}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label
                                    htmlFor={`translations.${locale}.summary`}
                                >
                                    Summary
                                </Label>
                                <textarea
                                    id={`translations.${locale}.summary`}
                                    name={`translations[${locale}][summary]`}
                                    defaultValue={
                                        document?.translations[locale]
                                            ?.summary ?? ''
                                    }
                                    className="border-input focus-visible:border-ring focus-visible:ring-ring/50 min-h-24 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                />
                                <InputError
                                    message={
                                        errors[`translations.${locale}.summary`]
                                    }
                                />
                            </div>
                        </div>
                    ))}

                    <Button disabled={processing}>{submitLabel}</Button>
                </>
            )}
        </Form>
    );
}

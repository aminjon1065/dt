import { Form } from '@inertiajs/react';
import BlockEditor from '@/components/cms/block-editor';
import MediaManager from '@/components/cms/media-manager';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { ContentBlock } from '@/lib/content-blocks';
import { SUPPORTED_LOCALES, type WayfinderFormAction } from '@/lib/locales';
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

type NewsFormData = {
    status: string;
    published_at: string | null;
    archived_at: string | null;
    featured_until: string | null;
    category_ids: number[];
    cover_url?: string | null;
    current_cover?: {
        id: number;
        name: string;
        url: string;
    } | null;
    translations: Record<'en' | 'tj' | 'ru', TranslationFields>;
};

type Category = {
    id: number;
    name: string;
};

type Props = {
    action: WayfinderFormAction;
    categories: Category[];
    availableStatuses: Array<{ value: string; label: string }>;
    newsItem?: NewsFormData;
    submitLabel: string;
};

export default function NewsForm({
    action,
    categories,
    availableStatuses,
    newsItem,
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
                                <Label htmlFor="status">Status</Label>
                                <NativeSelect
                                    id="status"
                                    name="status"
                                    defaultValue={newsItem?.status ?? 'draft'}
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
                                inputId="cover"
                                inputName="cover"
                                label="Cover image"
                                currentLabel="Current cover"
                                existingItems={
                                    newsItem?.current_cover
                                        ? [newsItem.current_cover]
                                        : []
                                }
                                removeInputName="remove_cover"
                                removeInputType="boolean"
                                error={errors.cover}
                                removeError={errors.remove_cover}
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
                                    defaultValue={newsItem?.published_at ?? ''}
                                />
                                <InputError message={errors.published_at} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="featured_until">
                                    Featured until
                                </Label>
                                <Input
                                    id="featured_until"
                                    name="featured_until"
                                    type="datetime-local"
                                    defaultValue={newsItem?.featured_until ?? ''}
                                />
                                <InputError message={errors.featured_until} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="archived_at">Archived at</Label>
                                <Input
                                    id="archived_at"
                                    name="archived_at"
                                    type="datetime-local"
                                    defaultValue={newsItem?.archived_at ?? ''}
                                />
                                <InputError message={errors.archived_at} />
                            </div>
                        </div>

                        <div className="grid gap-3">
                            <Label>Categories</Label>
                            <div className="grid gap-3 md:grid-cols-2">
                                {categories.map((category) => (
                                    <label
                                        key={category.id}
                                        className="flex items-center gap-3 rounded-md border px-3 py-2"
                                    >
                                        <Checkbox
                                            name="category_ids[]"
                                            value={String(category.id)}
                                            defaultChecked={newsItem?.category_ids.includes(
                                                category.id,
                                            )}
                                        />
                                        <span className="text-sm">
                                            {category.name}
                                        </span>
                                    </label>
                                ))}
                            </div>
                            <InputError message={errors.category_ids} />
                        </div>
                    </div>

                    {SUPPORTED_LOCALES.map((locale) => (
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
                                        newsItem?.translations[locale]?.title ??
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
                                        newsItem?.translations[locale]?.slug ??
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
                                        newsItem?.translations[locale]
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

                            <div className="grid gap-2">
                                <BlockEditor
                                    name={`translations[${locale}][content_blocks]`}
                                    label={`Content blocks (${locale.toUpperCase()})`}
                                    initialValue={newsItem?.translations[locale]?.content_blocks ?? undefined}
                                    legacyContent={newsItem?.translations[locale]?.content ?? ''}
                                />
                                <InputError
                                    message={
                                        errors[`translations.${locale}.content_blocks`]
                                    }
                                />
                            </div>

                            <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                                <div className="grid gap-2">
                                    <Label
                                        htmlFor={`translations.${locale}.seo_title`}
                                    >
                                        SEO title
                                    </Label>
                                    <Input
                                        id={`translations.${locale}.seo_title`}
                                        name={`translations[${locale}][seo_title]`}
                                        defaultValue={
                                            newsItem?.translations[locale]
                                                ?.seo_title ?? ''
                                        }
                                    />
                                    <InputError
                                        message={
                                            errors[
                                                `translations.${locale}.seo_title`
                                            ]
                                        }
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label
                                        htmlFor={`translations.${locale}.seo_description`}
                                    >
                                        SEO description
                                    </Label>
                                    <textarea
                                        id={`translations.${locale}.seo_description`}
                                        name={`translations[${locale}][seo_description]`}
                                        defaultValue={
                                            newsItem?.translations[locale]
                                                ?.seo_description ?? ''
                                        }
                                        className="border-input focus-visible:border-ring focus-visible:ring-ring/50 min-h-24 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                    />
                                    <InputError
                                        message={
                                            errors[
                                                `translations.${locale}.seo_description`
                                            ]
                                        }
                                    />
                                </div>
                            </div>
                        </div>
                    ))}

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

import { Form } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type MenuItemData = {
    id?: number;
    item_key: string;
    parent_item_key?: string | null;
    label: string;
    locale?: string | null;
    url?: string | null;
    route_name?: string | null;
    sort_order: number;
    is_active: boolean;
};

type MenuFormData = {
    name: string;
    slug: string;
    location?: string | null;
    items: MenuItemData[];
};

type Props = {
    action: any;
    menu?: MenuFormData;
    submitLabel: string;
};

export default function MenuForm({ action, menu, submitLabel }: Props) {
    const [itemKeys, setItemKeys] = useState<MenuItemData[]>(menu?.items ?? []);

    const addItem = () => {
        setItemKeys((current) => [
            ...current,
            {
                item_key: `new-${crypto.randomUUID()}`,
                label: '',
                locale: 'en',
                sort_order: current.length,
                is_active: true,
            },
        ]);
    };

    const removeItem = (itemKey: string) => {
        setItemKeys((current) =>
            current.filter((item) => item.item_key !== itemKey),
        );
    };

    return (
        <Form
            {...action}
            options={{ preserveScroll: true }}
            className="space-y-8"
        >
            {({ errors, processing }) => (
                <>
                    <div className="grid gap-6 rounded-xl border p-6">
                        <div className="grid gap-2 md:grid-cols-3 md:gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={menu?.name ?? ''}
                                />
                                <InputError message={errors.name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="slug">Slug</Label>
                                <Input
                                    id="slug"
                                    name="slug"
                                    defaultValue={menu?.slug ?? ''}
                                />
                                <InputError message={errors.slug} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="location">Location</Label>
                                <Input
                                    id="location"
                                    name="location"
                                    defaultValue={menu?.location ?? ''}
                                />
                                <InputError message={errors.location} />
                            </div>
                        </div>
                    </div>

                    <div className="grid gap-4 rounded-xl border p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <h2 className="text-lg font-semibold">Items</h2>
                                <p className="text-sm text-muted-foreground">
                                    Add navigation items and optional parent-child relationships.
                                </p>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={addItem}
                            >
                                <Plus />
                                Add item
                            </Button>
                        </div>

                        {itemKeys.map((item, index) => (
                            <div
                                key={item.item_key}
                                className="grid gap-4 rounded-lg border p-4"
                            >
                                <input
                                    type="hidden"
                                    name={`items[${index}][id]`}
                                    defaultValue={item.id ?? ''}
                                />
                                <input
                                    type="hidden"
                                    name={`items[${index}][item_key]`}
                                    defaultValue={item.item_key}
                                />

                                <div className="flex items-center justify-between">
                                    <h3 className="font-medium">
                                        Item {index + 1}
                                    </h3>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        onClick={() => removeItem(item.item_key)}
                                    >
                                        <Trash2 />
                                    </Button>
                                </div>

                                <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor={`items.${index}.label`}>
                                            Label
                                        </Label>
                                        <Input
                                            id={`items.${index}.label`}
                                            name={`items[${index}][label]`}
                                            defaultValue={item.label}
                                        />
                                        <InputError
                                            message={
                                                errors[`items.${index}.label`]
                                            }
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label
                                            htmlFor={`items.${index}.parent_item_key`}
                                        >
                                            Parent item
                                        </Label>
                                        <select
                                            id={`items.${index}.parent_item_key`}
                                            name={`items[${index}][parent_item_key]`}
                                            defaultValue={
                                                item.parent_item_key ?? ''
                                            }
                                            className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                        >
                                            <option value="">No parent</option>
                                            {itemKeys
                                                .filter(
                                                    (candidate) =>
                                                        candidate.item_key !==
                                                        item.item_key,
                                                )
                                                .map((candidate) => (
                                                    <option
                                                        key={
                                                            candidate.item_key
                                                        }
                                                        value={
                                                            candidate.item_key
                                                        }
                                                    >
                                                        {candidate.label ||
                                                            candidate.item_key}
                                                    </option>
                                                ))}
                                        </select>
                                        <InputError
                                            message={
                                                errors[
                                                    `items.${index}.parent_item_key`
                                                ]
                                            }
                                        />
                                    </div>
                                </div>

                                <div className="grid gap-2 md:grid-cols-4 md:gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor={`items.${index}.locale`}>
                                            Locale
                                        </Label>
                                        <select
                                            id={`items.${index}.locale`}
                                            name={`items[${index}][locale]`}
                                            defaultValue={item.locale ?? ''}
                                            className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                        >
                                            <option value="">All</option>
                                            <option value="en">English</option>
                                            <option value="tj">Tajik</option>
                                            <option value="ru">Russian</option>
                                        </select>
                                    </div>
                                    <div className="grid gap-2">
                                        <Label
                                            htmlFor={`items.${index}.sort_order`}
                                        >
                                            Sort order
                                        </Label>
                                        <Input
                                            id={`items.${index}.sort_order`}
                                            name={`items[${index}][sort_order]`}
                                            type="number"
                                            defaultValue={item.sort_order}
                                        />
                                    </div>
                                    <div className="grid gap-2 md:col-span-2">
                                        <Label className="mt-2">Active</Label>
                                        <label className="flex items-center gap-3 rounded-md border px-3 py-2">
                                            <Checkbox
                                                name={`items[${index}][is_active]`}
                                                value="1"
                                                defaultChecked={item.is_active}
                                            />
                                            <span className="text-sm">
                                                Visible in navigation
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor={`items.${index}.url`}>
                                            URL
                                        </Label>
                                        <Input
                                            id={`items.${index}.url`}
                                            name={`items[${index}][url]`}
                                            defaultValue={item.url ?? ''}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label
                                            htmlFor={`items.${index}.route_name`}
                                        >
                                            Route name
                                        </Label>
                                        <Input
                                            id={`items.${index}.route_name`}
                                            name={`items[${index}][route_name]`}
                                            defaultValue={item.route_name ?? ''}
                                        />
                                    </div>
                                </div>
                            </div>
                        ))}

                        {itemKeys.length === 0 && (
                            <div className="rounded-md border border-dashed px-4 py-8 text-center text-sm text-muted-foreground">
                                No menu items yet.
                            </div>
                        )}
                    </div>

                    <Button disabled={processing}>{submitLabel}</Button>
                </>
            )}
        </Form>
    );
}

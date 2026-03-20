import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type MediaItem = {
    id: number;
    name: string;
    url: string;
};

type Props = {
    inputId: string;
    inputName: string;
    label: string;
    error?: string;
    multiple?: boolean;
    currentLabel: string;
    emptyMessage?: string;
    existingItems?: MediaItem[];
    removeInputName?: string;
    removeInputType?: 'boolean' | 'array';
    removeError?: string;
};

export default function MediaManager({
    inputId,
    inputName,
    label,
    error,
    multiple = false,
    currentLabel,
    emptyMessage = 'No files uploaded yet.',
    existingItems = [],
    removeInputName,
    removeInputType = 'array',
    removeError,
}: Props) {
    const currentItem = existingItems[0];

    return (
        <div className="grid gap-3">
            <Label htmlFor={inputId}>{label}</Label>
            <Input
                id={inputId}
                name={inputName}
                type="file"
                multiple={multiple}
            />

            {existingItems.length > 0 ? (
                <div className="space-y-3 rounded-lg border border-dashed p-3">
                    <p className="text-sm font-medium">{currentLabel}</p>

                    {multiple ? (
                        existingItems.map((item) => (
                            <div
                                key={item.id}
                                className="flex flex-col gap-2 rounded-md border p-3 md:flex-row md:items-center md:justify-between"
                            >
                                <a
                                    href={item.url}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="text-sm underline"
                                >
                                    {item.name}
                                </a>

                                {removeInputName && (
                                    <label className="flex items-center gap-3 text-sm">
                                        <input
                                            type="checkbox"
                                            name={removeInputName}
                                            value={String(item.id)}
                                            className="h-4 w-4 rounded border"
                                        />
                                        <span>Remove on save</span>
                                    </label>
                                )}
                            </div>
                        ))
                    ) : currentItem ? (
                        <div className="space-y-3">
                            <a
                                href={currentItem.url}
                                target="_blank"
                                rel="noreferrer"
                                className="text-sm underline"
                            >
                                {currentItem.name}
                            </a>

                            {removeInputName && (
                                <label className="flex items-center gap-3 text-sm">
                                    <input
                                        type="checkbox"
                                        name={removeInputName}
                                        value="1"
                                        className="h-4 w-4 rounded border"
                                    />
                                    <span>Remove current file on save</span>
                                </label>
                            )}
                        </div>
                    ) : null}
                </div>
            ) : (
                <p className="text-sm text-muted-foreground">{emptyMessage}</p>
            )}

            <InputError message={error} />
            {removeInputName && (
                <InputError
                    message={removeError}
                    className={removeInputType === 'boolean' ? undefined : ''}
                />
            )}
        </div>
    );
}

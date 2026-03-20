import { useState } from 'react';
import { ArrowDown, ArrowUp, Plus, Trash2 } from 'lucide-react';
import ContentBlockPreview from '@/components/content-block-preview';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    ContentBlock,
    ContentBlockType,
    createEmptyBlock,
    initialBlocks,
    serializeBlocks,
} from '@/lib/content-blocks';

const blockTypeLabels: Record<ContentBlockType, string> = {
    paragraph: 'Paragraph',
    heading: 'Heading',
    quote: 'Quote',
    list: 'List',
    html: 'HTML block',
};

export default function BlockEditor({
    name,
    label,
    initialValue,
    legacyContent,
}: {
    name: string;
    label: string;
    initialValue?: ContentBlock[] | null;
    legacyContent?: string | null;
}) {
    const [blocks, setBlocks] = useState<ContentBlock[]>(() => initialBlocks(initialValue, legacyContent));

    const updateBlock = (index: number, updater: (block: ContentBlock) => ContentBlock) => {
        setBlocks((currentBlocks) => currentBlocks.map((block, blockIndex) => blockIndex === index ? updater(block) : block));
    };

    const moveBlock = (index: number, direction: -1 | 1) => {
        setBlocks((currentBlocks) => {
            const targetIndex = index + direction;

            if (targetIndex < 0 || targetIndex >= currentBlocks.length) {
                return currentBlocks;
            }

            const reorderedBlocks = [...currentBlocks];
            const [selectedBlock] = reorderedBlocks.splice(index, 1);

            reorderedBlocks.splice(targetIndex, 0, selectedBlock);

            return reorderedBlocks;
        });
    };

    const addBlock = (type: ContentBlockType) => {
        setBlocks((currentBlocks) => [...currentBlocks, createEmptyBlock(type)]);
    };

    const removeBlock = (index: number) => {
        setBlocks((currentBlocks) => {
            const nextBlocks = currentBlocks.filter((_, blockIndex) => blockIndex !== index);

            return nextBlocks.length > 0 ? nextBlocks : [createEmptyBlock('paragraph')];
        });
    };

    return (
        <div className="grid gap-6 rounded-xl border p-6">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 className="text-base font-semibold">{label}</h3>
                    <p className="text-sm text-muted-foreground">
                        Build content with reusable blocks and preview it before publishing.
                    </p>
                </div>

                <div className="flex flex-wrap gap-2">
                    {(['paragraph', 'heading', 'quote', 'list', 'html'] as ContentBlockType[]).map((type) => (
                        <Button
                            key={type}
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => addBlock(type)}
                        >
                            <Plus className="size-4" />
                            {blockTypeLabels[type]}
                        </Button>
                    ))}
                </div>
            </div>

            <input type="hidden" name={name} value={serializeBlocks(blocks)} />

            <div className="grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)]">
                <div className="space-y-4">
                    {blocks.map((block, index) => (
                        <section key={block.id} className="space-y-4 rounded-xl border bg-muted/10 p-4">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div className="flex items-center gap-2">
                                    <span className="rounded-full bg-background px-3 py-1 text-xs font-medium uppercase tracking-[0.18em] text-muted-foreground">
                                        Block {index + 1}
                                    </span>
                                    <span className="text-sm font-medium">{blockTypeLabels[block.type]}</span>
                                </div>

                                <div className="flex items-center gap-2">
                                    <Button type="button" variant="outline" size="icon" onClick={() => moveBlock(index, -1)}>
                                        <ArrowUp className="size-4" />
                                    </Button>
                                    <Button type="button" variant="outline" size="icon" onClick={() => moveBlock(index, 1)}>
                                        <ArrowDown className="size-4" />
                                    </Button>
                                    <Button type="button" variant="outline" size="icon" onClick={() => removeBlock(index)}>
                                        <Trash2 className="size-4" />
                                    </Button>
                                </div>
                            </div>

                            {block.type === 'heading' && (
                                <div className="grid gap-4 md:grid-cols-[10rem_minmax(0,1fr)]">
                                    <div className="grid gap-2">
                                        <Label>Heading level</Label>
                                        <select
                                            value={block.level ?? 2}
                                            onChange={(event) => updateBlock(index, (currentBlock) => ({
                                                ...currentBlock,
                                                level: Number(event.target.value) as 2 | 3 | 4,
                                            }))}
                                            className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                        >
                                            <option value={2}>H2</option>
                                            <option value={3}>H3</option>
                                            <option value={4}>H4</option>
                                        </select>
                                    </div>

                                    <div className="grid gap-2">
                                        <Label>Heading text</Label>
                                        <Input
                                            value={block.content ?? ''}
                                            onChange={(event) => updateBlock(index, (currentBlock) => ({
                                                ...currentBlock,
                                                content: event.target.value,
                                            }))}
                                        />
                                    </div>
                                </div>
                            )}

                            {(block.type === 'paragraph' || block.type === 'quote' || block.type === 'html') && (
                                <div className="grid gap-2">
                                    <Label>{block.type === 'html' ? 'HTML source' : 'Text'}</Label>
                                    <textarea
                                        value={block.content ?? ''}
                                        onChange={(event) => updateBlock(index, (currentBlock) => ({
                                            ...currentBlock,
                                            content: event.target.value,
                                        }))}
                                        className="border-input focus-visible:border-ring focus-visible:ring-ring/50 min-h-32 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                    />
                                </div>
                            )}

                            {block.type === 'list' && (
                                <div className="space-y-3">
                                    <Label>List items</Label>
                                    {(block.items ?? []).map((item, itemIndex) => (
                                        <div key={`${block.id}-${itemIndex}`} className="flex gap-2">
                                            <Input
                                                value={item}
                                                onChange={(event) => updateBlock(index, (currentBlock) => ({
                                                    ...currentBlock,
                                                    items: (currentBlock.items ?? []).map((listItem, listItemIndex) => (
                                                        listItemIndex === itemIndex ? event.target.value : listItem
                                                    )),
                                                }))}
                                            />
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="icon"
                                                onClick={() => updateBlock(index, (currentBlock) => {
                                                    const items = (currentBlock.items ?? []).filter((_, listItemIndex) => listItemIndex !== itemIndex);

                                                    return {
                                                        ...currentBlock,
                                                        items: items.length > 0 ? items : [''],
                                                    };
                                                })}
                                            >
                                                <Trash2 className="size-4" />
                                            </Button>
                                        </div>
                                    ))}
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={() => updateBlock(index, (currentBlock) => ({
                                            ...currentBlock,
                                            items: [...(currentBlock.items ?? []), ''],
                                        }))}
                                    >
                                        <Plus className="size-4" />
                                        Add item
                                    </Button>
                                </div>
                            )}
                        </section>
                    ))}
                </div>

                <section className="rounded-xl border bg-background p-4">
                    <h4 className="text-sm font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                        Live preview
                    </h4>
                    <div className="mt-4">
                        <ContentBlockPreview
                            blocks={blocks.filter((block) => block.type === 'list'
                                ? (block.items ?? []).some((item) => item.trim() !== '')
                                : (block.content ?? '').trim() !== '')}
                        />
                    </div>
                </section>
            </div>
        </div>
    );
}

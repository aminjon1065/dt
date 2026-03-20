export type ContentBlockType = 'paragraph' | 'heading' | 'quote' | 'list' | 'html';

export type ContentBlock = {
    id: string;
    type: ContentBlockType;
    content?: string;
    level?: 2 | 3 | 4;
    items?: string[];
};

const blockTypes: ContentBlockType[] = ['paragraph', 'heading', 'quote', 'list', 'html'];

const createBlockId = () => {
    if (typeof crypto !== 'undefined' && 'randomUUID' in crypto) {
        return crypto.randomUUID();
    }

    return `block-${Math.random().toString(36).slice(2, 10)}`;
};

export function createEmptyBlock(type: ContentBlockType = 'paragraph'): ContentBlock {
    return type === 'list'
        ? { id: createBlockId(), type, items: [''] }
        : type === 'heading'
            ? { id: createBlockId(), type, content: '', level: 2 }
            : { id: createBlockId(), type, content: '' };
}

export function normalizeBlocks(value: unknown): ContentBlock[] {
    if (! Array.isArray(value)) {
        return [];
    }

    return value
        .filter((block): block is Record<string, unknown> => typeof block === 'object' && block !== null)
        .map((block) => {
            const type = blockTypes.includes(block.type as ContentBlockType)
                ? (block.type as ContentBlockType)
                : 'paragraph';

            if (type === 'list') {
                return {
                    id: typeof block.id === 'string' ? block.id : createBlockId(),
                    type,
                    items: Array.isArray(block.items)
                        ? block.items.map((item) => String(item))
                        : [''],
                } satisfies ContentBlock;
            }

            if (type === 'heading') {
                return {
                    id: typeof block.id === 'string' ? block.id : createBlockId(),
                    type,
                    content: typeof block.content === 'string' ? block.content : '',
                    level: [2, 3, 4].includes(Number(block.level))
                        ? (Number(block.level) as 2 | 3 | 4)
                        : 2,
                } satisfies ContentBlock;
            }

            return {
                id: typeof block.id === 'string' ? block.id : createBlockId(),
                type,
                content: typeof block.content === 'string' ? block.content : '',
            } satisfies ContentBlock;
        });
}

export function initialBlocks(blocks?: ContentBlock[] | null, legacyContent?: string | null): ContentBlock[] {
    const normalizedBlocks = normalizeBlocks(blocks ?? []);

    if (normalizedBlocks.length > 0) {
        return normalizedBlocks;
    }

    if (legacyContent && legacyContent.trim() !== '') {
        return [{
            id: createBlockId(),
            type: 'html',
            content: legacyContent,
        }];
    }

    return [createEmptyBlock('paragraph')];
}

export function serializeBlocks(blocks: ContentBlock[]): string {
    return JSON.stringify(
        normalizeBlocks(blocks)
            .map((block) => {
                if (block.type === 'list') {
                    const items = (block.items ?? []).filter((item) => item.trim() !== '');

                    return items.length > 0 ? { ...block, items } : null;
                }

                return (block.content ?? '').trim() !== '' ? block : null;
            })
            .filter((block): block is ContentBlock => block !== null),
    );
}

import { useEditor, EditorContent } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import Placeholder from '@tiptap/extension-placeholder';
import Link from '@tiptap/extension-link';
import {
    Bold,
    Italic,
    Underline as UnderlineIcon,
    Strikethrough,
    Heading2,
    Heading3,
    List,
    ListOrdered,
    Quote,
    Link as LinkIcon,
    Minus,
    Undo,
    Redo,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import type { ContentBlock } from '@/lib/content-blocks';

function blocksToHtml(blocks: ContentBlock[]): string {
    return blocks
        .map((block) => {
            if (block.type === 'heading') {
                const tag = `h${block.level ?? 2}`;
                return `<${tag}>${block.content ?? ''}</${tag}>`;
            }
            if (block.type === 'quote') {
                return `<blockquote><p>${block.content ?? ''}</p></blockquote>`;
            }
            if (block.type === 'list') {
                const items = (block.items ?? []).map((item) => `<li>${item}</li>`).join('');
                return `<ul>${items}</ul>`;
            }
            if (block.type === 'html') {
                return block.content ?? '';
            }
            return `<p>${block.content ?? ''}</p>`;
        })
        .join('');
}

type ToolbarButtonProps = {
    onClick: () => void;
    active?: boolean;
    disabled?: boolean;
    title: string;
    children: React.ReactNode;
};

function ToolbarButton({ onClick, active, disabled, title, children }: ToolbarButtonProps) {
    return (
        <button
            type="button"
            title={title}
            disabled={disabled}
            onClick={onClick}
            className={cn(
                'rounded p-1.5 text-sm transition-colors hover:bg-muted',
                active && 'bg-muted text-foreground',
                !active && 'text-muted-foreground',
                disabled && 'pointer-events-none opacity-40',
            )}
        >
            {children}
        </button>
    );
}

function ToolbarDivider() {
    return <div className="mx-1 h-5 w-px bg-border" />;
}

export default function RichTextEditor({
    name,
    label,
    initialHtml,
    fallbackBlocks,
    placeholder = 'Write something...',
}: {
    name: string;
    label: string;
    initialHtml?: string | null;
    fallbackBlocks?: ContentBlock[] | null;
    placeholder?: string;
}) {
    const resolvedInitialHtml =
        initialHtml && initialHtml.trim() !== ''
            ? initialHtml
            : fallbackBlocks && fallbackBlocks.length > 0
              ? blocksToHtml(fallbackBlocks)
              : '';

    const editor = useEditor({
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3, 4] },
            }),
            Underline,
            Link.configure({
                openOnClick: false,
                HTMLAttributes: { rel: 'noopener noreferrer' },
            }),
            Placeholder.configure({ placeholder }),
        ],
        content: resolvedInitialHtml,
        editorProps: {
            attributes: {
                class: 'prose prose-stone max-w-none min-h-48 px-4 py-3 focus:outline-none',
            },
        },
    });

    const setLink = () => {
        if (!editor) {
            return;
        }
        const previous = editor.getAttributes('link').href as string | undefined;
        const url = window.prompt('URL', previous);
        if (url === null) {
            return;
        }
        if (url === '') {
            editor.chain().focus().extendMarkRange('link').unsetLink().run();
            return;
        }
        editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
    };

    return (
        <div className="grid gap-2">
            <label className="text-sm font-medium">{label}</label>

            <div className="overflow-hidden rounded-lg border focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50">
                {/* Toolbar */}
                <div className="flex flex-wrap items-center gap-0.5 border-b bg-muted/40 px-2 py-1.5">
                    <ToolbarButton
                        title="Bold"
                        onClick={() => editor?.chain().focus().toggleBold().run()}
                        active={editor?.isActive('bold')}
                    >
                        <Bold className="size-4" />
                    </ToolbarButton>
                    <ToolbarButton
                        title="Italic"
                        onClick={() => editor?.chain().focus().toggleItalic().run()}
                        active={editor?.isActive('italic')}
                    >
                        <Italic className="size-4" />
                    </ToolbarButton>
                    <ToolbarButton
                        title="Underline"
                        onClick={() => editor?.chain().focus().toggleUnderline().run()}
                        active={editor?.isActive('underline')}
                    >
                        <UnderlineIcon className="size-4" />
                    </ToolbarButton>
                    <ToolbarButton
                        title="Strikethrough"
                        onClick={() => editor?.chain().focus().toggleStrike().run()}
                        active={editor?.isActive('strike')}
                    >
                        <Strikethrough className="size-4" />
                    </ToolbarButton>

                    <ToolbarDivider />

                    <ToolbarButton
                        title="Heading 2"
                        onClick={() => editor?.chain().focus().toggleHeading({ level: 2 }).run()}
                        active={editor?.isActive('heading', { level: 2 })}
                    >
                        <Heading2 className="size-4" />
                    </ToolbarButton>
                    <ToolbarButton
                        title="Heading 3"
                        onClick={() => editor?.chain().focus().toggleHeading({ level: 3 }).run()}
                        active={editor?.isActive('heading', { level: 3 })}
                    >
                        <Heading3 className="size-4" />
                    </ToolbarButton>

                    <ToolbarDivider />

                    <ToolbarButton
                        title="Bullet list"
                        onClick={() => editor?.chain().focus().toggleBulletList().run()}
                        active={editor?.isActive('bulletList')}
                    >
                        <List className="size-4" />
                    </ToolbarButton>
                    <ToolbarButton
                        title="Ordered list"
                        onClick={() => editor?.chain().focus().toggleOrderedList().run()}
                        active={editor?.isActive('orderedList')}
                    >
                        <ListOrdered className="size-4" />
                    </ToolbarButton>
                    <ToolbarButton
                        title="Blockquote"
                        onClick={() => editor?.chain().focus().toggleBlockquote().run()}
                        active={editor?.isActive('blockquote')}
                    >
                        <Quote className="size-4" />
                    </ToolbarButton>

                    <ToolbarDivider />

                    <ToolbarButton title="Link" onClick={setLink} active={editor?.isActive('link')}>
                        <LinkIcon className="size-4" />
                    </ToolbarButton>
                    <ToolbarButton
                        title="Horizontal rule"
                        onClick={() => editor?.chain().focus().setHorizontalRule().run()}
                    >
                        <Minus className="size-4" />
                    </ToolbarButton>

                    <ToolbarDivider />

                    <ToolbarButton
                        title="Undo"
                        onClick={() => editor?.chain().focus().undo().run()}
                        disabled={!editor?.can().undo()}
                    >
                        <Undo className="size-4" />
                    </ToolbarButton>
                    <ToolbarButton
                        title="Redo"
                        onClick={() => editor?.chain().focus().redo().run()}
                        disabled={!editor?.can().redo()}
                    >
                        <Redo className="size-4" />
                    </ToolbarButton>
                </div>

                {/* Editor area */}
                <EditorContent editor={editor} />
            </div>

            {/* Hidden input for form submission */}
            <input type="hidden" name={name} value={editor?.getHTML() ?? ''} />
        </div>
    );
}

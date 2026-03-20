import type { ContentBlock } from '@/lib/content-blocks';

export default function ContentBlockPreview({
    blocks,
    emptyMessage = 'Preview will appear as you build the page.',
}: {
    blocks: ContentBlock[];
    emptyMessage?: string;
}) {
    if (blocks.length === 0) {
        return <p className="text-sm text-muted-foreground">{emptyMessage}</p>;
    }

    return (
        <div className="prose prose-stone max-w-none">
            {blocks.map((block) => {
                if (block.type === 'heading') {
                    const HeadingTag = `h${block.level ?? 2}` as 'h2' | 'h3' | 'h4';

                    return <HeadingTag key={block.id}>{block.content}</HeadingTag>;
                }

                if (block.type === 'quote') {
                    return (
                        <blockquote key={block.id}>
                            <p>{block.content}</p>
                        </blockquote>
                    );
                }

                if (block.type === 'list') {
                    return (
                        <ul key={block.id}>
                            {(block.items ?? []).map((item, index) => (
                                <li key={`${block.id}-${index}`}>{item}</li>
                            ))}
                        </ul>
                    );
                }

                if (block.type === 'html') {
                    return (
                        <div
                            key={block.id}
                            dangerouslySetInnerHTML={{ __html: block.content ?? '' }}
                        />
                    );
                }

                return <p key={block.id}>{block.content}</p>;
            })}
        </div>
    );
}

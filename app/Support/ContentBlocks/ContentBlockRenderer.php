<?php

namespace App\Support\ContentBlocks;

use Illuminate\Support\Arr;

class ContentBlockRenderer
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function normalize(mixed $blocks): array
    {
        if (! is_array($blocks)) {
            return [];
        }

        return collect($blocks)
            ->filter(fn (mixed $block): bool => is_array($block) && filled($block['type'] ?? null))
            ->map(function (array $block): array {
                $type = (string) ($block['type'] ?? 'paragraph');

                return match ($type) {
                    'heading' => [
                        'id' => (string) ($block['id'] ?? uniqid('block_', true)),
                        'type' => 'heading',
                        'content' => (string) ($block['content'] ?? ''),
                        'level' => (int) Arr::get($block, 'level', 2),
                    ],
                    'list' => [
                        'id' => (string) ($block['id'] ?? uniqid('block_', true)),
                        'type' => 'list',
                        'items' => collect(Arr::get($block, 'items', []))
                            ->filter(fn (mixed $item): bool => is_string($item))
                            ->map(fn (string $item): string => $item)
                            ->values()
                            ->all(),
                    ],
                    'quote' => [
                        'id' => (string) ($block['id'] ?? uniqid('block_', true)),
                        'type' => 'quote',
                        'content' => (string) ($block['content'] ?? ''),
                    ],
                    'html' => [
                        'id' => (string) ($block['id'] ?? uniqid('block_', true)),
                        'type' => 'html',
                        'content' => (string) ($block['content'] ?? ''),
                    ],
                    default => [
                        'id' => (string) ($block['id'] ?? uniqid('block_', true)),
                        'type' => 'paragraph',
                        'content' => (string) ($block['content'] ?? ''),
                    ],
                };
            })
            ->filter(function (array $block): bool {
                if ($block['type'] === 'list') {
                    return count($block['items']) > 0;
                }

                return filled($block['content'] ?? null);
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    public static function toHtml(array $blocks): ?string
    {
        $normalizedBlocks = self::normalize($blocks);

        if ($normalizedBlocks === []) {
            return null;
        }

        return collect($normalizedBlocks)
            ->map(function (array $block): string {
                return match ($block['type']) {
                    'heading' => self::renderHeading($block),
                    'list' => self::renderList($block),
                    'quote' => self::renderQuote($block),
                    'html' => (string) ($block['content'] ?? ''),
                    default => self::renderParagraph($block),
                };
            })
            ->implode("\n");
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected static function renderHeading(array $block): string
    {
        $level = in_array((int) ($block['level'] ?? 2), [2, 3, 4], true)
            ? (int) $block['level']
            : 2;

        return "<h{$level}>".self::formatText((string) ($block['content'] ?? ''))."</h{$level}>";
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected static function renderParagraph(array $block): string
    {
        return '<p>'.self::formatText((string) ($block['content'] ?? '')).'</p>';
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected static function renderQuote(array $block): string
    {
        return '<blockquote><p>'.self::formatText((string) ($block['content'] ?? '')).'</p></blockquote>';
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected static function renderList(array $block): string
    {
        $items = collect($block['items'] ?? [])
            ->map(fn (mixed $item): string => '<li>'.self::formatText((string) $item).'</li>')
            ->implode('');

        return '<ul>'.$items.'</ul>';
    }

    protected static function formatText(string $content): string
    {
        return nl2br(e(trim($content)), false);
    }
}

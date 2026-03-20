<?php

namespace App\Http\Requests;

use App\Models\News;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', News::class);
    }

    protected function prepareForValidation(): void
    {
        $translations = collect($this->input('translations', []))
            ->map(function (mixed $translation): mixed {
                if (! is_array($translation)) {
                    return $translation;
                }

                if (is_string($translation['content_blocks'] ?? null)) {
                    $translation['content_blocks'] = json_decode($translation['content_blocks'], true) ?? [];
                }

                return $translation;
            })
            ->all();

        $this->merge([
            'translations' => $translations,
        ]);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in($this->allowedStatuses())],
            'published_at' => ['nullable', 'date'],
            'archived_at' => ['nullable', 'date'],
            'featured_until' => ['nullable', 'date'],
            'cover' => ['nullable', 'image', 'max:10240'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:news_categories,id'],
            'translations' => ['required', 'array:en,tj,ru'],
            'translations.en' => ['required', 'array'],
            'translations.tj' => ['required', 'array'],
            'translations.ru' => ['required', 'array'],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['required', 'string', 'max:255'],
            'translations.*.summary' => ['nullable', 'string'],
            'translations.*.content' => ['nullable', 'string'],
            'translations.*.content_blocks' => ['nullable', 'array'],
            'translations.*.content_blocks.*.type' => ['required', Rule::in(['paragraph', 'heading', 'quote', 'list', 'html'])],
            'translations.*.content_blocks.*.content' => ['nullable', 'string'],
            'translations.*.content_blocks.*.level' => ['nullable', 'integer', Rule::in([2, 3, 4])],
            'translations.*.content_blocks.*.items' => ['nullable', 'array'],
            'translations.*.content_blocks.*.items.*' => ['nullable', 'string'],
            'translations.*.seo_title' => ['nullable', 'string', 'max:255'],
            'translations.*.seo_description' => ['nullable', 'string'],
            'translations.en.slug' => [
                'required', 'string', 'max:255',
                Rule::unique('news_translations', 'slug')->where('locale', 'en'),
            ],
            'translations.tj.slug' => [
                'required', 'string', 'max:255',
                Rule::unique('news_translations', 'slug')->where('locale', 'tj'),
            ],
            'translations.ru.slug' => [
                'required', 'string', 'max:255',
                Rule::unique('news_translations', 'slug')->where('locale', 'ru'),
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function allowedStatuses(): array
    {
        if ($this->user()->getAllPermissions()->contains('name', 'news.publish')) {
            return ['draft', 'in_review', 'published', 'archived'];
        }

        return ['draft', 'in_review'];
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\ContentStatus;
use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('page'));
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
        $page = $this->route('page');

        return [
            'parent_id' => ['nullable', 'exists:pages,id', Rule::notIn([$page->id])],
            'template' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in($this->allowedStatuses())],
            'published_at' => ['nullable', 'date'],
            'archived_at' => ['nullable', 'date'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_home' => ['required', 'boolean'],
            'cover' => ['nullable', 'image', 'max:10240'],
            'remove_cover' => ['nullable', 'boolean'],
            'translations' => ['required', 'array:'.implode(',', config('app.supported_locales'))],
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
                'required',
                'string',
                'max:255',
                Rule::unique('page_translations', 'slug')->where('locale', 'en')->ignore(
                    $page->translation('en')?->id
                ),
            ],
            'translations.tj.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('page_translations', 'slug')->where('locale', 'tj')->ignore(
                    $page->translation('tj')?->id
                ),
            ],
            'translations.ru.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('page_translations', 'slug')->where('locale', 'ru')->ignore(
                    $page->translation('ru')?->id
                ),
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function allowedStatuses(): array
    {
        if ($this->user()->can('publish', Page::class)) {
            return ContentStatus::values();
        }

        return ContentStatus::editableValues();
    }
}

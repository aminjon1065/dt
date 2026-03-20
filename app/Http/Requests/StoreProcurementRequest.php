<?php

namespace App\Http\Requests;

use App\Models\Procurement;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProcurementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Procurement::class);
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

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reference_number' => ['required', 'string', 'max:255', 'unique:procurements,reference_number'],
            'procurement_type' => ['required', Rule::in(['goods', 'services', 'works', 'consulting', 'other'])],
            'status' => ['required', Rule::in($this->allowedStatuses())],
            'published_at' => ['nullable', 'date'],
            'closing_at' => ['nullable', 'date', 'after_or_equal:published_at'],
            'archived_at' => ['nullable', 'date', 'after_or_equal:published_at'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:20480'],
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
                Rule::unique('procurement_translations', 'slug')->where('locale', 'en'),
            ],
            'translations.tj.slug' => [
                'required', 'string', 'max:255',
                Rule::unique('procurement_translations', 'slug')->where('locale', 'tj'),
            ],
            'translations.ru.slug' => [
                'required', 'string', 'max:255',
                Rule::unique('procurement_translations', 'slug')->where('locale', 'ru'),
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function allowedStatuses(): array
    {
        if ($this->user()->getAllPermissions()->contains('name', 'procurements.publish')) {
            return ['planned', 'open', 'closed', 'awarded', 'cancelled', 'archived'];
        }

        return ['planned'];
    }
}

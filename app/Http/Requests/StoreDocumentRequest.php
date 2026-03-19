<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Document::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document_category_id' => ['required', 'integer', 'exists:document_categories,id'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'file_type' => ['nullable', 'string', 'max:50'],
            'document_date' => ['nullable', 'date'],
            'published_at' => ['nullable', 'date'],
            'archived_at' => ['nullable', 'date', 'after_or_equal:published_at'],
            'file' => ['required', 'file', 'max:20480'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:document_tags,id'],
            'translations' => ['required', 'array:en,tj,ru'],
            'translations.en' => ['required', 'array'],
            'translations.tj' => ['required', 'array'],
            'translations.ru' => ['required', 'array'],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['required', 'string', 'max:255'],
            'translations.*.summary' => ['nullable', 'string'],
            'translations.en.slug' => [
                'required', 'string', 'max:255',
                Rule::unique('document_translations', 'slug')->where('locale', 'en'),
            ],
            'translations.tj.slug' => [
                'required', 'string', 'max:255',
                Rule::unique('document_translations', 'slug')->where('locale', 'tj'),
            ],
            'translations.ru.slug' => [
                'required', 'string', 'max:255',
                Rule::unique('document_translations', 'slug')->where('locale', 'ru'),
            ],
        ];
    }
}

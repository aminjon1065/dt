<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('document'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Document $document */
        $document = $this->route('document');

        return [
            'document_category_id' => ['required', 'integer', 'exists:document_categories,id'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'file_type' => ['nullable', 'string', 'max:50'],
            'document_date' => ['nullable', 'date'],
            'published_at' => ['nullable', 'date'],
            'archived_at' => ['nullable', 'date', 'after_or_equal:published_at'],
            'file' => ['nullable', 'file', 'max:20480'],
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
                'required',
                'string',
                'max:255',
                Rule::unique('document_translations', 'slug')->where('locale', 'en')->ignore(
                    $document->translation('en')?->id
                ),
            ],
            'translations.tj.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('document_translations', 'slug')->where('locale', 'tj')->ignore(
                    $document->translation('tj')?->id
                ),
            ],
            'translations.ru.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('document_translations', 'slug')->where('locale', 'ru')->ignore(
                    $document->translation('ru')?->id
                ),
            ],
        ];
    }
}

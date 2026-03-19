<?php

namespace App\Http\Requests;

use App\Models\Page;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('page'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $page = $this->route('page');

        return [
            'parent_id' => ['nullable', 'exists:pages,id', Rule::notIn([$page->id])],
            'template' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'published_at' => ['nullable', 'date'],
            'archived_at' => ['nullable', 'date'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_home' => ['required', 'boolean'],
            'cover' => ['nullable', 'image', 'max:10240'],
            'translations' => ['required', 'array:en,tj,ru'],
            'translations.en' => ['required', 'array'],
            'translations.tj' => ['required', 'array'],
            'translations.ru' => ['required', 'array'],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['required', 'string', 'max:255'],
            'translations.*.summary' => ['nullable', 'string'],
            'translations.*.content' => ['nullable', 'string'],
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
}

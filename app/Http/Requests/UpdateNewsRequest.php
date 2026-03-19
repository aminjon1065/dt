<?php

namespace App\Http\Requests;

use App\Models\News;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('news'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var News $news */
        $news = $this->route('news');

        return [
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
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
            'translations.*.seo_title' => ['nullable', 'string', 'max:255'],
            'translations.*.seo_description' => ['nullable', 'string'],
            'translations.en.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('news_translations', 'slug')->where('locale', 'en')->ignore(
                    $news->translation('en')?->id
                ),
            ],
            'translations.tj.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('news_translations', 'slug')->where('locale', 'tj')->ignore(
                    $news->translation('tj')?->id
                ),
            ],
            'translations.ru.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('news_translations', 'slug')->where('locale', 'ru')->ignore(
                    $news->translation('ru')?->id
                ),
            ],
        ];
    }
}

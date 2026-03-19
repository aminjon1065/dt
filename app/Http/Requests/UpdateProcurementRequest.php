<?php

namespace App\Http\Requests;

use App\Models\Procurement;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProcurementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('procurement'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Procurement $procurement */
        $procurement = $this->route('procurement');

        return [
            'reference_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('procurements', 'reference_number')->ignore($procurement->id),
            ],
            'procurement_type' => ['required', Rule::in(['goods', 'services', 'works', 'consulting', 'other'])],
            'status' => ['required', Rule::in(['planned', 'open', 'closed', 'awarded', 'cancelled', 'archived'])],
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
            'translations.*.seo_title' => ['nullable', 'string', 'max:255'],
            'translations.*.seo_description' => ['nullable', 'string'],
            'translations.en.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('procurement_translations', 'slug')->where('locale', 'en')->ignore(
                    $procurement->translation('en')?->id
                ),
            ],
            'translations.tj.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('procurement_translations', 'slug')->where('locale', 'tj')->ignore(
                    $procurement->translation('tj')?->id
                ),
            ],
            'translations.ru.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('procurement_translations', 'slug')->where('locale', 'ru')->ignore(
                    $procurement->translation('ru')?->id
                ),
            ],
        ];
    }
}

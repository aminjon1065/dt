<?php

namespace App\Http\Requests;

use App\Models\News;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', News::class);
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(['draft', 'in_review', 'published', 'archived'])],
        ];
    }
}

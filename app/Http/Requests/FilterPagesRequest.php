<?php

namespace App\Http\Requests;

use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterPagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Page::class);
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(['draft', 'in_review', 'published', 'archived'])],
        ];
    }
}

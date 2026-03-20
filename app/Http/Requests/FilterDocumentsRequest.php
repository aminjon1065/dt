<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterDocumentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Document::class);
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(['draft', 'in_review', 'published', 'archived'])],
        ];
    }
}

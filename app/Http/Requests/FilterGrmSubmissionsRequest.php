<?php

namespace App\Http\Requests;

use App\Models\GrmSubmission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterGrmSubmissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', GrmSubmission::class);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['new', 'under_review', 'in_progress', 'resolved', 'closed'])],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}

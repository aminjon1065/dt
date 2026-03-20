<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGrmSubmissionWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('grm_submission'));
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['new', 'under_review', 'in_progress', 'resolved', 'closed'])],
        ];
    }
}

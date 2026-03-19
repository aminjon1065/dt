<?php

namespace App\Http\Requests;

use App\Models\GrmSubmission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGrmSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('grm_submission'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var GrmSubmission $submission */
        $submission = $this->route('grm_submission');

        return [
            'reference_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('grm_submissions', 'reference_number')->ignore($submission->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'status' => ['required', Rule::in(['new', 'under_review', 'in_progress', 'resolved', 'closed'])],
            'submitted_at' => ['required', 'date'],
            'reviewed_at' => ['nullable', 'date', 'after_or_equal:submitted_at'],
            'resolved_at' => ['nullable', 'date', 'after_or_equal:submitted_at'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'note' => ['nullable', 'string'],
        ];
    }
}

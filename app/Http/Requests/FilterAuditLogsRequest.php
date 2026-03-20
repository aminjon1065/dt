<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterAuditLogsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'event' => ['nullable', Rule::in(['created', 'updated', 'deleted', 'workflow-updated', 'public-subscribed', 'public-unsubscribed'])],
            'model' => ['nullable', Rule::in(['page', 'news', 'document', 'procurement', 'grm', 'staff', 'subscription'])],
            'actor' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ];
    }
}

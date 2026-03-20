<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterDashboardActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event' => ['nullable', 'string', Rule::in(['created', 'updated', 'deleted', 'public-subscribed', 'public-unsubscribed'])],
            'model' => ['nullable', 'string', Rule::in(['page', 'news', 'document', 'procurement', 'grm', 'staff', 'subscription'])],
            'actor' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ];
    }
}

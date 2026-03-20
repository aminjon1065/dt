<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterSubscriptionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('subscriptions.view');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'unsubscribed', 'bounced'])],
            'locale' => ['nullable', Rule::in(['en', 'tj', 'ru'])],
        ];
    }
}

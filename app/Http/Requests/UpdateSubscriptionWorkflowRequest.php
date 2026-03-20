<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriptionWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('subscriptions.update');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['active', 'unsubscribed', 'bounced'])],
        ];
    }
}

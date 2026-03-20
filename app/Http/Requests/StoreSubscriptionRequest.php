<?php

namespace App\Http\Requests;

use App\Models\Subscription;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Subscription::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255', 'unique:subscriptions,email'],
            'locale' => ['required', Rule::in(['en', 'tj', 'ru'])],
            'status' => ['required', Rule::in(['active', 'unsubscribed', 'bounced'])],
            'source' => ['nullable', 'string', 'max:255'],
            'subscribed_at' => ['nullable', 'date'],
            'unsubscribed_at' => ['nullable', 'date'],
            'last_notified_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}

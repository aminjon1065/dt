<?php

namespace App\Http\Requests;

use App\Models\Procurement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProcurementWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('publish', Procurement::class);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['open', 'closed', 'awarded', 'cancelled', 'archived'])],
        ];
    }
}

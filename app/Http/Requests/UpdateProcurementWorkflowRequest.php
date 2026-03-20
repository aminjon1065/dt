<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProcurementWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->getAllPermissions()->contains('name', 'procurements.publish');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['open', 'closed', 'awarded', 'cancelled', 'archived'])],
        ];
    }
}

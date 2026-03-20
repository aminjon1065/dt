<?php

namespace App\Http\Requests;

use App\Models\Procurement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterProcurementsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Procurement::class);
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(['planned', 'open', 'closed', 'awarded', 'cancelled', 'archived'])],
        ];
    }
}

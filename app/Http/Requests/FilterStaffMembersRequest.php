<?php

namespace App\Http\Requests;

use App\Models\StaffMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterStaffMembersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', StaffMember::class);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
            'parent_id' => ['nullable', 'integer', 'exists:staff_members,id'],
        ];
    }
}

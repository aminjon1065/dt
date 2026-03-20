<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        $status = $this->string('status')->toString();

        if (in_array($status, ['published', 'archived'], true)) {
            return $this->user()->getAllPermissions()->contains('name', 'news.publish');
        }

        return $this->user()->getAllPermissions()->contains('name', 'news.update');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['draft', 'in_review', 'published', 'archived'])],
        ];
    }
}

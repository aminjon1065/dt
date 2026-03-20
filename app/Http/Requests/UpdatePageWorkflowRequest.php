<?php

namespace App\Http\Requests;

use App\Enums\ContentStatus;
use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        $status = $this->string('status')->toString();

        if (in_array($status, [ContentStatus::Published->value, ContentStatus::Archived->value], true)) {
            return $this->user()->can('publish', Page::class);
        }

        return $this->user()->can('pages.update');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ContentStatus::class)],
        ];
    }
}

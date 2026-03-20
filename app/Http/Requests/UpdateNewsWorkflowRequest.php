<?php

namespace App\Http\Requests;

use App\Enums\ContentStatus;
use App\Models\News;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        $status = $this->string('status')->toString();

        if (in_array($status, [ContentStatus::Published->value, ContentStatus::Archived->value], true)) {
            return $this->user()->can('publish', News::class);
        }

        return $this->user()->can('news.update');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ContentStatus::class)],
        ];
    }
}
